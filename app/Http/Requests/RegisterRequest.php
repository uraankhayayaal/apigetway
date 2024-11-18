<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class RegisterRequest
{
    public function __construct(
        public string $username,
        public string $email,
        public string $phone,
        public string $password,
        public bool $isAgreeMarketing,
        public bool $isAgreePolicy,
    ) {}
}
