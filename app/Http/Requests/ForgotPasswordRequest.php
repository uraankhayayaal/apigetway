<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class ForgotPasswordRequest
{
    public function __construct(
        public string $email,
    ) {}
}
