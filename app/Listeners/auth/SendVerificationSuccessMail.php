<?php

namespace App\Listeners\auth;


use App\Jobs\auth\SendEmailVerifiedNotificationJob;
use App\Mail\auth\EmailVerifiedMail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Mail;

class SendVerificationSuccessMail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Verified $event): void
    {
        // send verification success mail using emails queue worker
        SendEmailVerifiedNotificationJob::dispatch($event->user)->onQueue('auth');
    }
}
