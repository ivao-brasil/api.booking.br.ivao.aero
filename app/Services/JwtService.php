<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;

class JwtService
{
    private static $alg = 'HS256';
    public static function encode($data) {
        $payload = array(
            "iss" => env('APP_URL'),
            "aud" => env('APP_URL'),
            "iat" => time(),
            "exp" => time() + 24 * 60 * 60,
            "sub" => $data
        );

        $key = env('JWT_SECRET');

        return JWT::encode($payload, $key, self::$alg);
    }

    public static function decode(String $jwt) {
        return JWT::decode($jwt, env('JWT_SECRET'), [self::$alg]);
    }
}
