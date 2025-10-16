<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IvaoLoginService
{
    private const ENDPOINT = 'https://api.ivao.aero/v2/oauth/token';

    public static function getAccessTokenFromAuthCode($authCode, $redirectUri, $codeVerifier = '', $nonce = '')
    {
        $data = [
            'grant_type' => 'authorization_code',
            'code' => $authCode,
            'redirect_uri' => $redirectUri,
            'client_id' => env('IVAO_CLIENT_ID'),
            'client_secret' => env('IVAO_CLIENT_SECRET'),
            'scope' => 'profile',
            'code_verifier' => $codeVerifier,
            'nonce' => $nonce,
            'refresh_token' => '',
            'password' => '',
            'username' => ''
        ];

        $response = Http::post(self::ENDPOINT, $data);

        return $response->json();
    }

    public static function getAuthData($ivaoToken)
    {
        $finalEndpoint = self::ENDPOINT . $ivaoToken;
        $data = Http::get($finalEndpoint);
        return $data->json();
    }
}
