<?php

declare(strict_types=1);

namespace App\Http\Responses;

final class UserResponse
{
    /** @param string[] $roles */
    public function __construct(
        public int $id,
        public string $email,
        public string $phone,
        public array $roles,
        public int $createdAt,
    ) {}
}
