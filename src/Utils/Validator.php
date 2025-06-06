<?php

namespace UthApi\Utils;

class Validator
{
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validatePassword(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }
        return $errors;
    }

    public static function validateUsername(string $username): array
    {
        $errors = [];

        if (strlen($username) < 3) {
            $errors[] = "Username must be at least 3 characters long";
        }

        if (strlen($username) > 50) {
            $errors[] = "Username must not exceed 50 characters";
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = "Username can only contain letters, numbers, and underscores";
        }

        return $errors;
    }

    public static function sanitizeString(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function validateDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
        return $d && $d->format('Y-m-d H:i:s') === $date;
    }

    public static function validateStatus(string $status): bool
    {
        return in_array($status, ['Pending', 'InProgress', 'Completed']);
    }
}
