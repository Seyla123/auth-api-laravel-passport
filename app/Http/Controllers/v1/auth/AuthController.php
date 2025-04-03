<?php

namespace App\Http\Controllers\v1\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\auth\ResetPasswordRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    
    public function __construct(private AuthService $authService)
    {
        
    }

    // login
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        $oAuthToken = $this->authService->attemptLogin($credentials);

        if (!$oAuthToken || !isset($oAuthToken['refresh_token'])) {
            return $this->errorResponse(__('auth.login_failed'), 401);
        }

        return $this->tokenResponse(
            $oAuthToken,
            __('auth.login_success'),
            $oAuthToken['refresh_token'],
            200
        );
    }

    // register
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->register([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password
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

            // Revoke the refresh token
            $success = $this->authService->logout($token->id);
            if (!$success) {
                return $this->errorResponse(__('auth.logout_failed'), 500);
            }

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
            $success = $this->authService->forgotPassword($request->email);

            if (!$success) {
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
            $success = $this->authService->resetPassword(
                $request->only('email', 'password', 'password_confirmation', 'token')
            );

            if (!$success) {
                return $this->errorResponse(__('auth.reset_password_failed'), 400);
            }

            return $this->successResponse(null, __('auth.reset_password_success'), 200);
        } catch (\Throwable $th) {
            \Log::error("Failed to reset password: " . $th->getMessage());
            return $this->errorResponse(__('auth.reset_password_failed'), 500);
        }
    }
}
