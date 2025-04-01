<?php

namespace App\Http\Controllers\v1\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateAvatarRequest;
use App\Services\Profile\ProfileService;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profileService)
    {
    }
    // current user profile
    public function getProfile(): JsonResponse
    {
        try {
            $user = $this->profileService->getProfile();

            if (!$user) {
                return $this->errorResponse(__('profile.user_not_found'), 401);
            }

            return $this->successResponse($user, __('success'), 200);
        } catch (\Throwable $th) {
            \Log::error("Failed to get current user: " . $th->getMessage());
            return $this->errorResponse(__('failed'), 500);
        }
    }

    // update user avatar
    public function updateAvatar(UpdateAvatarRequest $request): JsonResponse
    {
        try {
            $path = $this->profileService->updateAvatar($request->file('avatar'));
            if (!$path) {
                return $this->errorResponse(__('profile.avatar_update_failed'), 400);
            }

            return $this->successResponse(['avatar' => $path], __('profile.avatar_updated'), 200);
        } catch (\Throwable $th) {
            \Log::error("Avatar update failed: " . $th->getMessage());
            return $this->errorResponse(__('profile.avatar_update_failed'), 400);
        }
    }
}

