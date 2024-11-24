<?php

declare(strict_types=1);

namespace App\Http\Responses;

final class TokenResponse
{
    public function __construct(
        public ?string $accessToken,
    ) {}

    public function __toString(): string
    {
        return json_encode($this);
    }
}
