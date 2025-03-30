<?php

namespace App\Services;

use Laravel\Passport\Client as OClient;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\RefreshTokenRepository;

class AuthService
{
    protected OClient $client;

    public function __construct()
    {
        $this->client = OClient::where('password_client', 1)->first();
        if (!$this->client) {
            throw new \RuntimeException('OAuth client not found');
        }
    }

    public function getTokenAndRefreshToken(string $email, string $password)
    {
        $params = [
            'grant_type' => 'password',
            'client_id' => $this->client->id,
            'client_secret' => $this->client->secret,
            'username' => $email,
            'password' => $password,
            'scope' => '*'
        ];

        return $this->makeTokenRequest($params);
    }

    public function refreshToken(string $refreshToken)
    {
        $params = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $this->client->id,
            'client_secret' => $this->client->secret,
            'scope' => '*',
        ];

        return $this->makeTokenRequest($params);
    }

    public function revokeToken(string $tokenId)
    {
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);
        
        // Revoke access token
        $tokenRepository->revokeAccessToken($tokenId);

        // Revoke refresh token
        $refreshTokenRepository->revokeRefreshToken($tokenId);
    }

    /**
     * Make a token request to the OAuth server
     * 
     * @param array $params Parameters for the token request
     * @return array Decoded response from the OAuth server
     * 
     * This method handles both password grant and refresh token requests:
     * 1. Adds parameters to the current request
     * 2. Creates a new request to oauth/token endpoint
     * 3. Dispatches the request through Laravel's router
     * 4. For password grants, if refresh token is missing, updates client and retries
     */
    protected function makeTokenRequest(array $params)
    {
        request()->request->add($params);
        $request = Request::create('oauth/token', 'POST');

        $response = Route::dispatch($request);
        $result = json_decode($response->getContent(), true);

        if (!isset($result['refresh_token']) && $params['grant_type'] === 'password') {
            $this->client->update(['personal_access_client' => true]);
            $response = Route::dispatch($request);
            $result = json_decode($response->getContent(), true);
        }

        return $result;
    }
}