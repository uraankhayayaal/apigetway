<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class ResetPasswordRequest
{
    public function __construct(
        public string $passwordResetToken,
        public string $password,
    ) {}
}
