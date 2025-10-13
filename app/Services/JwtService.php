<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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

    public static function decode(string $jwt) {
        $key = new Key(env('JWT_SECRET'), self::$alg);
        return JWT::decode($jwt, $key);
    }
}
