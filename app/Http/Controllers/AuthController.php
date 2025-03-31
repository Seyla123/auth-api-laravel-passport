<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }
    // login
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Generate OAuth token and refresh token
            $oAuthToken = $this->authService->getTokenAndRefreshToken($request->email, $request->password);

            if (!isset($oAuthToken['refresh_token'])) {
                return $this->errorResponse('login fail', 401);
            }

            // Return the access token and set refresh token in cookie
            return $this->tokenResponse(
                $oAuthToken,
                __('auth.login_success'),
                $oAuthToken['refresh_token'],
                200
            );

        } else {
            return $this->errorResponse(__('auth.login_failed'), 401);
        }
    }
    // register
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);
            return $this->successResponse($user, __('auth.register_success'), 201);

        } catch (\Throwable $th) {
            \Log::error("Register failed: " . $th->getMessage());
            return $this->errorResponse(__('auth.register_failed'), 400);
        }
    }

    // logout
    public function logout(Request $request): JsonResponse
    {
        try {
            $token = $request->user()->token();

            if (!$token) {
                return $this->errorResponse(__('auth.token_not_found'), 401);
            }

            // Revoke the access and refresh tokens
            $this->authService->revokeToken($token->id);

            return $this->successResponse([], __('auth.logout_success'), 200)
                ->cookie('refresh_token', null, -1);

        } catch (\Throwable $th) {
            \Log::error("Logout failed: " . $th->getMessage());
            return $this->errorResponse(__('auth.logout_failed'), 500);
        }
    }
    // refresh
    public function refresh(Request $request): JsonResponse
    {
        try {
            $refreshToken = $request->cookie('refresh_token');

            if (!$refreshToken) {
                return $this->errorResponse(__('auth.token_not_found'), 400);
            }

            $oAuthToken = $this->authService->refreshToken($refreshToken);

            if (!isset($oAuthToken['refresh_token'])) {
                return $this->errorResponse(__('auth.token_invalid'), 401);
            }

            // Return the new access token and set refresh token in cookie
            return $this->tokenResponse(
                $oAuthToken,
                __('auth.refresh_token_success'),
                $oAuthToken['refresh_token'],
                200
            );

        } catch (\Throwable $th) {
            \Log::error("Token refresh failed: " . $th->getMessage());
            return $this->errorResponse(__('auth.refresh_token_failed'), 401);
        }
    }
    // current user
    public function currentUser(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return $this->errorResponse(__('auth.user_not_found'), 401);
            }
            
            return $this->successResponse($user, __('auth.success'), 200);
        } catch (\Throwable $th) {
            \Log::error("Failed to get current user: " . $th->getMessage());
            return $this->errorResponse(__('auth.failed'), 500);
        }
    }
}
