<?php

namespace App\Http\Controllers\v1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class VerifyEmailController extends Controller
{
    public function verify($id): JsonResponse
    {
        $user = User::where('id', $id)->get()->first();

        // Check if the user exists and if the signature is valid
        if (!$user->getEmailForVerification() || !request()->hasValidSignature()) {
            return $this->errorResponse(__('auth.invalid_verification_link'), 401);
        }

        // Check if the user's email is already verified
        if ($user->hasVerifiedEmail()) {
            return $this->errorResponse(__('auth.already_verified'), 401);
        }

        // Mark email as verified and trigger verification event
        if ($user->markEmailAsVerified()) {
            // Trigger Verified event to send welcome and verification emails
            event(new Verified($user));
        }

        return $this->successResponse(null, __('auth.verify_email_success'), 200);
    }

    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check if the user's email is already verified
        if ($user->hasVerifiedEmail()) {
            return $this->errorResponse(__('auth.already_verified'), 401);
        }

        // Send the verification email
        $user->sendEmailVerificationNotification();

        return $this->successResponse(null, __('auth.resend_verify_email_success'), 200);
    }
}
