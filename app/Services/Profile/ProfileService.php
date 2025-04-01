<?php
namespace App\Services\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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
}