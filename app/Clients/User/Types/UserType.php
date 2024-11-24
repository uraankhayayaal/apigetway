<?php

declare(strict_types=1);

namespace App\Clients\User\Types;

class UserType
{
    /** @param string[] $roles */
    public function __construct(
        public int $id,
        public int $status,
        public int $createdAt,
        public int $updatedAt,
        public string $email,
        public string $phone,
        public array $roles,
    ) {}
}
