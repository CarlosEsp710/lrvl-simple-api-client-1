<?php

namespace App\Http\Controllers;

use App\Traits\Token;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, Token;

    public function resolveAuthorization()
    {
        if (auth()->user()->accessToken->expires_at <= now()) {
            $this->refreshToken();
        }
    }
}
