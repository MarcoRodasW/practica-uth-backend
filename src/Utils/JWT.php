<?php

namespace UthApi\Utils;

use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;
use Exception;

class JWT
{
    private static string $secret;
    private static int $expire;

    public static function init(): void
    {
        self::$secret = getenv('JWT_SECRET');
        self::$expire = (int)getenv('JWT_EXPIRE');
    }

    public static function encode(array $payload): string
    {
        $payload['iat'] = time();
        $payload['exp'] = time() + self::$expire;

        return FirebaseJWT::encode($payload, self::$secret, 'HS256');
    }

    public static function decode(string $token): ?object
    {
        try {
            return FirebaseJWT::decode($token, new Key(self::$secret, 'HS256'));
        } catch (Exception $e) {
            return null;
        }
    }

    public static function getTokenFromHeader(): ?string
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
