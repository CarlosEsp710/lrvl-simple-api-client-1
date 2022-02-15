<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PostController extends Controller
{
    public function store()
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . auth()->user()->accessToken->access_token
        ])->post('http://127.0.0.1:8000/api/v1/posts', [
            'name' => 'Post de prueba desde el cliente',
            'slug' => 'post-de-prueba-desde-el-cliente',
            'extract' => 'Extracto',
            'body' => 'Cuerpo del post',
            'category_id' => 2
        ]);

        return $response->json();
    }
}
