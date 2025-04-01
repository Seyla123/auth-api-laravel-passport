<?php
namespace App\Services\Profile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Filesystem\Factory as Storage;

/**
 * Class ProfileService
 * 
 * Service class responsible for handling user profile related operations.
 * Provides functionality to retrieve and manage user profile information
 */
class ProfileService
{
    /**
     *
     * @param Storage $storage The storage factory instance for handling file operations
     */
    public function __construct(protected Storage $storage)
    {
    }

    /**
     * Get the currently authenticated user
     * 
     * @return User|null Current user or null if not authenticated
     */
    public function getProfile(): ?User
    {
        return Auth::user();
    }

    /**
     * Update the authenticated user's avatar
     * 
     * @param UploadedFile $avatar The new avatar file to upload
     * @return string The URL of the newly uploaded avatar
     */
    public function updateAvatar(UploadedFile $avatar): string
    {
        $user = Auth::user();
        $path = $avatar->store(
            'avatars/' . $user->id,
            's3'
        );

        // Delete old avatar if exists from s3
        if ($user instanceof User) {
            $this->deleteOldAvatar($user);
        }

        // Update user's avatar path to s3 and save
        $user->avatar = $this->storage->disk('s3')->url($path);
        $user->save();

        return $user->avatar;
    }

    /**
     * Delete the user's old avatar from storage
     * 
     * @param User $user The user whose avatar should be deleted
     * @return void
     */
    private function deleteOldAvatar(User $user): void
    {
        if (!$user->avatar)
            return;

        try {
            $urlParts = parse_url($user->avatar);
            $oldPath = ltrim($urlParts['path'], '/');

            if (!empty($oldPath)) {
                $this->storage->disk('s3')->delete($oldPath);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to delete old avatar', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}