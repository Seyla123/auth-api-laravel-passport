<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Client as OClient;

class AuthController extends Controller
{
    // login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $client = OClient::where('password_client', 1)->first();
            $params = [
                'grant_type' => 'password',
                'client_id' => $client->id,
                'client_secret' => $client->secret,
                'username' => $request->email,
                'password' => $request->password,
                'scope' => '*',
            ];
            $request->request->add($params);
            $request->headers->set("Accept", "application/json");
            $request->headers->set("Content-Type", "application/json");

            $proxy = Request::create('oauth/token', 'POST');

            $res = Route::dispatch($proxy);

            $response = json_decode($res->getContent(), true);

            return response()->json([
                "status" => "success",
                "message" => "login suc",
                "data" => [
                    "token_type" => $response['token_type'],
                    "expires_in" => $response['expires_in'],
                    "access_token" => $response['access_token'],
                ]
            ], $res->getStatusCode())->cookie(
                    'refresh_token',
                    $response['refresh_token'],
                    60 * 24 * 30, // 30 days
                    null,
                    null,
                    true, // secure
                    true  // httpOnly
                );
            ;
        } else {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    }
    // register
    public function register(Request $request)
    {

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        return response()->json(['data' => $user, "message" => 'register suc'], 201);
    }

    // logout

    // refresh
    public function refresh(Request $request)
    {
        $refresh_token = $request->cookie('refresh_token');

        if (!$refresh_token) {
            return response()->json(['error' => 'Refresh token not found'], 400);
        }

        $client = OClient::where('password_client', 1)->first();

        $params = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token,
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => '*',
        ];

        $request->request->add($params);
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('Content-Type', 'application/json');

        $proxy = Request::create('oauth/token', 'POST');
        $response = Route::dispatch($proxy);

        $data = json_decode($response->getContent(), true);

        if (!$response->isSuccessful()) {
            return response()->json(['error' => 'Failed to refresh token'], $response->getStatusCode());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Token refreshed successfully',
            'data' => [
                'token_type' => $data['token_type'],
                'expires_in' => $data['expires_in'],
                'access_token' => $data['access_token'],
            ]
        ], 200)->cookie(
                'refresh_token',
                $data['refresh_token'],
                60 * 24 * 30, // 30 days
                null,
                null,
                true, // secure
                true  // httpOnly
            );
    }
}
