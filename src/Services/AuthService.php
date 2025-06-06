<?php

namespace UthApi\Services;

use UthApi\Models\User;
use UthApi\Utils\JWT;
use UthApi\Utils\Validator;

class AuthService
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function register(array $data): array
    {
        $errors = [];

        if (empty($data['username'])) {
            $errors[] = "Username is required";
        } else {
            $usernameErrors = Validator::validateUsername($data['username']);
            $errors = array_merge($errors, $usernameErrors);
        }

        if (empty($data['email'])) {
            $errors[] = "Email is required";
        } elseif (!Validator::validateEmail($data['email'])) {
            $errors[] = "Invalid email format";
        }

        if (empty($data['password'])) {
            $errors[] = "Password is required";
        } else {
            $passwordErrors = Validator::validatePassword($data['password']);
            $errors = array_merge($errors, $passwordErrors);
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        if ($this->userModel->emailExists($data['email'])) {
            return ['success' => false, 'errors' => ['Email already exists']];
        }

        if ($this->userModel->usernameExists($data['username'])) {
            return ['success' => false, 'errors' => ['Username already exists']];
        }

        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $success = $this->userModel->create(
            Validator::sanitizeString($data['username']),
            Validator::sanitizeString($data['email']),
            $passwordHash
        );

        if ($success) {
            return ['success' => true, 'message' => 'User registered successfully'];
        }

        return ['success' => false, 'errors' => ['Registration failed']];
    }

    public function login(array $data): array
    {
        if (empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'errors' => ['Email and password are required']];
        }

        $user = $this->userModel->findByEmail($data['email']);

        if (!$user || !password_verify($data['password'], $user['password_hash'])) {
            return ['success' => false, 'errors' => ['Invalid credentials']];
        }

        $token = JWT::encode([
            'user_id' => $user['id'],
            'email' => $user['email']
        ]);

        return [
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email']
            ]
        ];
    }

    public function updateProfile(int $userId, array $data): array
    {
        $errors = [];
        $username = null;
        $email = null;
        $passwordHash = null;

        // Validate username if provided
        if (!empty($data['username'])) {
            $usernameErrors = Validator::validateUsername($data['username']);
            if (!empty($usernameErrors)) {
                $errors = array_merge($errors, $usernameErrors);
            } elseif ($this->userModel->usernameExists($data['username'], $userId)) {
                $errors[] = "Username already exists";
            } else {
                $username = Validator::sanitizeString($data['username']);
            }
        }

        // Validate email if provided
        if (!empty($data['email'])) {
            if (!Validator::validateEmail($data['email'])) {
                $errors[] = "Invalid email format";
            } elseif ($this->userModel->emailExists($data['email'], $userId)) {
                $errors[] = "Email already exists";
            } else {
                $email = Validator::sanitizeString($data['email']);
            }
        }

        // Validate password if provided
        if (!empty($data['password'])) {
            if (empty($data['new_password'])) {
                $errors[] = "New password is required when changing password";
            } else {
                $passwordErrors = Validator::validatePassword($data['new_password']);
                if (!empty($passwordErrors)) {
                    $errors = array_merge($errors, $passwordErrors);
                } else {
                    // Verify current password
                    $user = $this->userModel->findById($userId);
                    if (!$user || !password_verify($data['password'], $user['password_hash'] ?? '')) {
                        $errors[] = "Current password is incorrect";
                    } else {
                        $passwordHash = password_hash($data['new_password'], PASSWORD_DEFAULT);
                    }
                }
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $success = $this->userModel->updateProfile($userId, $username, $email, $passwordHash);

        if ($success) {
            return ['success' => true, 'message' => 'Profile updated successfully'];
        }

        return ['success' => false, 'errors' => ['Profile update failed']];
    }
}
