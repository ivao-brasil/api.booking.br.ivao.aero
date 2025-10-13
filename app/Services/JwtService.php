<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;

class JwtService
{
    public static function encode($data) {
        $payload = array(
            "iss" => env('APP_URL'),
            "aud" => env('APP_URL'),
            "iat" => time(),
            "exp" => time() + 24 * 60 * 60,
            "sub" => $data
        );

        $key = env('JWT_SECRET');
        $alg = 'HS256';
        return JWT::encode($payload, $key, $alg);
    }

    public static function decode(String $jwt) {
        return JWT::decode($jwt, env('JWT_SECRET'), ['HS256']);
    }
}
