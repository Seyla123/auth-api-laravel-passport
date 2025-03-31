<?php

namespace App\Listeners\auth;

use App\Jobs\auth\SendEmailWelcomeJob;
use Illuminate\Auth\Events\Verified;

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
    public function handle(Verified $event): void
    {
        // send welcome mail using emails queue worker with 10 seconds delay
        SendEmailWelcomeJob::dispatch($event->user)->onQueue('auth')->delay(now()->addSeconds(10));
    }
}
