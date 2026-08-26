<?php

namespace App\Repositories\Auth\Interfaces;

use App\Models\User;

interface AuthRepositoryInterface
{
    public function register(array $data): array;
    public function verify_code(array $data): array;
    public function resend_code(array $data): array;
    public function login(array $data): array;
    public function login_with_google(string $idToken): array;
    public function updateOrCreateByGoogle(array $googleData): User;
    public function logout($user): array;

}
