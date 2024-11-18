<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class LoginRequest
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $rememberMe = false,
    ) {}
}
