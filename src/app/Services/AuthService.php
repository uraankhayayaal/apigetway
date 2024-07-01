<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(private UserRepository $userRepository) {}

    public function login($credentials) : string
    {
        return Auth::attempt($credentials);
    }

    public function register(string $name, string $email, string $password) : string
    {
        $user = $this->userRepository->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password)
        ]);

        return Auth::login($user);
    }

    public function refreshToken() : string
    {
        return Auth::refresh();
    }

    public function getUser() : ?Authenticatable
    {
        return Auth::user();
    }

    public function logout() : void
    {
        Auth::logout();
    }
}