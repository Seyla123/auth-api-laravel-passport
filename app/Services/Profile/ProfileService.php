<?php
namespace App\Services\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Class ProfileService
 * 
 * Service class responsible for handling user profile related operations.
 * Provides functionality to retrieve and manage user profile information
 */
class ProfileService
{
    /**
     * Get the currently authenticated user
     * 
     * @return User|null Current user or null if not authenticated
     */
    public function getProfile(): ?User
    {
        return Auth::user();
    }

    // update user avatar
    public function updateAvatar($avatar): string
    {
        $user = Auth::user();
        $path = $avatar->store(
            'avatars/' . $user->id,
            's3'
        );

        // Delete old avatar if exists
        if ($user->avatar) {
            // Storage::disk('s3')->delete($user->avatar);
            try {
                $urlParts = parse_url($user->avatar);
                $oldPath = ltrim($urlParts['path'], '/');

                if (!empty($oldPath)) {
                    Storage::disk('s3')->delete($oldPath);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to delete old avatar: ' . $e->getMessage());
            }
        }

        $user->avatar = Storage::disk('s3')->url($path);

        $user->save();

        return $user->avatar;
    }

}