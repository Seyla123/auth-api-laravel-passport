<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse($data, $message = null, $code = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse($message, $code): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
        ], $code);
    }

    protected function tokenResponse($tokenData, $message = null, $refreshToken = null, $code = 200): JsonResponse
    {
        $response = response()->json([
            'status' => 'success',
            'message' => $message ?? 'Token generated successfully',
            'data' => [
                'token_type' => $tokenData['token_type'],
                'expires_in' => $tokenData['expires_in'],
                'access_token' => $tokenData['access_token'],
            ]
        ], $code);

        if ($refreshToken) {
            $response->cookie(
                'refresh_token',
                $refreshToken,
                60 * 24 * 30, // 30 days
                null,
                null,
                true, // secure
                true  // httpOnly
            );
        }

        return $response;
    }
}