<?php

namespace App\Listeners\auth;


use App\Jobs\auth\SendEmailVerifiedNotificationJob;
use Illuminate\Auth\Events\Verified;

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
        // send verification success mail using auth queue worker
        SendEmailVerifiedNotificationJob::dispatch($event->user)->onQueue('auth');
    }
}
