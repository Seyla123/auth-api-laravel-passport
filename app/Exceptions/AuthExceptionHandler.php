<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Auth\AuthenticationException;

class AuthExceptionHandler
{
    use ApiResponse;
    public function handle(AuthenticationException $e, $request)
    {
        return $this->errorResponse(__('auth.unauthenticated'), 401);
    }
}