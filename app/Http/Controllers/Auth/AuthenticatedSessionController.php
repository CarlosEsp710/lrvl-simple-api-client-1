<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->post('http://127.0.0.1:8000/api/v1/login', [
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if ($response->status() == 404) {
            $response = $response->json();

            return back()->withErrors($response['message']);
        }

        $service = $response->json();

        $user = User::updateOrCreate([
            'email' => $request->email,
        ], $service['data']);

        if (!$user->accessToken()->count()) {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post('http://127.0.0.1:8000/oauth/token', [
                'grant_type' => 'password',
                'client_id' => '9599cbbb-e722-4bb7-a957-6df786f75637',
                'client_secret' => 'wWZOfxWHTzpOC9uqbwU6QftyiI9dA2VQiY82CvRa',
                'username' => $request->email,
                'password' => $request->password,
            ]);

            $access_token = $response->json();

            $user->accessToken()->create([
                'service_id' => $service['data']['id'],
                'access_token' => $access_token['access_token'],
                'refresh_token' => $access_token['refresh_token'],
                'expires_at' => now()->addSecond($access_token['expires_in']),
            ]);
        }

        Auth::login($user, $request->remember);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
