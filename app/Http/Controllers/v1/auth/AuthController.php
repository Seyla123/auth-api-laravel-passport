<?php

namespace App\Http\Controllers\v1\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

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

            // create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);

            // send email verifaction
            event(new Registered($user));

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

            // Refresh the access token
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

    // forgot password
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            // find user
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return $this->errorResponse(__(''), 400);
            }

            // Send the password reset link email
            $status = Password::sendResetLink($request->only('email'));

            // Check if the password reset link was sent successfully
            if ($status !== Password::RESET_LINK_SENT) {
                return $this->errorResponse(__('auth.forgot_password_failed'), 400);
            }

            return $this->successResponse(null, __('forgot_password_success'), 200);

        } catch (\Throwable $th) {
            \Log::error("Failed to send forgot password email: " . $th->getMessage());
            return $this->errorResponse(__('auth.forgot_password_failed'), 500);
        }
    }

    // reset password
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();

                    // not work
                    event(new PasswordReset($user));
                }
            );

            // Check if the password reset was successful
            if ($status !== Password::PASSWORD_RESET) {
                return $this->errorResponse(__('auth.reset_password_failed'), 400);
            }

            return $this->successResponse(null, __('auth.reset_password_success'), 200);

        } catch (\Throwable $th) {
            \Log::error("Failed to send reset password : " . $th->getMessage());
            return $this->errorResponse(__('auth.reset_password_failed'), 500);
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
