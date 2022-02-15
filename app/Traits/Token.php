<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Http;

trait Token
{
    public function getAccessToken(User $user, $service)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->post('http://127.0.0.1:8000/oauth/token', [
            'grant_type' => 'password',
            'client_id' => config('services.server.client_id'),
            'client_secret' => config('services.server.client_secret'),
            'username' => request('email'),
            'password' => request('password'),
        ]);

        $access_token = $response->json();

        $user->accessToken()->create([
            'service_id' => $service['data']['id'],
            'access_token' => $access_token['access_token'],
            'refresh_token' => $access_token['refresh_token'],
            'expires_at' => now()->addSecond($access_token['expires_in']),
        ]);
    }

    public function refreshToken()
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->post('http://127.0.0.1:8000/oauth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => config('services.server.client_id'),
            'client_secret' => config('services.server.client_secret'),
            'refresh_token' => auth()->user()->accessToken->refresh_token,
        ]);

        $access_token = $response->json();

        auth()->user()->accessToken->update([
            'access_token' => $access_token['access_token'],
            'refresh_token' => $access_token['refresh_token'],
            'expires_at' => now()->addSecond($access_token['expires_in']),
        ]);
    }
}
