<?php

namespace App\Jobs\auth;

use App\Jobs\Job;
use App\Mail\auth\WelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendEmailWelcomeJob extends Job
{
    /**
     * Create a new job instance.
     */
    public function __construct(public User $user)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info("(Job) , Sending welcome email to user {$this->user->id}");
        Mail::to($this->user->email)->send(new WelcomeMail($this->user));
    }

    public function failed(Throwable $exception): void
    {
        \Log::error("Failed to send welcome email to user {$this->user->id}", [
            'error' => $exception->getMessage()
        ]);
    }
}
