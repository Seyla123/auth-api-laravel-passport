<?php

namespace App\Http\Controllers\v1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\AuthService;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{

    public function __construct(private AuthService $authService)
    {

    }
    public function redirect($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
            // Find or create user
            $user = User::updateOrCreate(
                ['email' => $socialUser->getEmail()],
                [
                    'name' => $socialUser->getName(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'email_verified_at' => now(),
                    'password' => bcrypt($socialUser->getId()),
                    'avatar' => $socialUser->getAvatar(),
                ]
            );

            // Generate token
            $tokenResult = $this->authService->getTokenAndRefreshToken(
                $user->email,
                $socialUser->getId()
            );

            if (!$tokenResult || isset($tokenResult['refresh_token'])) {
                return $this->errorResponse(__('auth.login_failed'), 401);
            }

            return $this->tokenResponse($tokenResult, __('auth.login_success'), $tokenResult['refresh_token']);

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }

    }
}
