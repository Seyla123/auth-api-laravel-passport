<?php

namespace App\Listeners\auth;

use App\Events\auth\UserRegistered;
use App\Jobs\auth\SendEmailWelcomeJob;


class SendWelcomeMail
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
    public function handle(UserRegistered $event): void
    {
        // send welcome mail using auth queue worker
        SendEmailWelcomeJob::dispatch($event->user)->onQueue('auth');
    }
}
