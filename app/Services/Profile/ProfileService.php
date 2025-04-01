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
     * @var User The authenticated user instance
     */
    protected User $user;

    /**
     * ProfileService constructor
     * Initializes the service with storage and authenticated user
     * 
     * @param Storage $storage The storage factory instance for handling file operations
     * @throws \Exception When no authenticated user is found
     */
    public function __construct(protected Storage $storage)
    {
        $this->user = Auth::user();
    }

    /**
     * Get the currently authenticated user
     * 
     * @return User|null Current user or null if not authenticated
     */
    public function getProfile(): User
    {
        return $this->user;
    }

    /**
     * Update the authenticated user's avatar
     * 
     * @param UploadedFile $avatar The new avatar file to upload
     * @return string The URL of the newly uploaded avatar
     */
    public function updateAvatar(UploadedFile $avatar): string
    {
        if (!$this->user) {
            throw new \Exception('User not found');
        }

        $path = $avatar->store(
            'avatars/' . $this->user->id,
            's3'
        );

        // Delete old avatar if exists from s3
        $this->deleteOldAvatar($this->user);

        // Update user's avatar path to s3 and save
        $this->user->avatar = $this->storage->disk('s3')->url($path);
        $this->user->save();

        return $this->user->avatar;
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

    /**
     * Update the authenticated user's profile information
     *
     * @param array $data The new profile data to update
     * @return User The updated user instance
     */
    public function updateProfile(array $data): User
    {
        \DB::beginTransaction();
        try {
            // Update avatar if provided
            if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
                $avatarUrl = $this->updateAvatar($data['avatar']);
                $data['avatar'] = $avatarUrl;
            }

            // Update user's profile information
            $this->user->update($data);

            \DB::commit();
            return $this->user->fresh();

        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}