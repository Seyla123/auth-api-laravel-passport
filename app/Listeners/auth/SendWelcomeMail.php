<?php

namespace App\Listeners\auth;

use App\Mail\auth\WelcomeMail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Mail;

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
        $email = $event->user->email;
        //send welcome email
        Mail::to($email)->queue(new WelcomeMail($event->user));
    }
}
