<?php

declare(strict_types=1);

namespace App\Services;

use App\Clients\User\UserClient;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\Facades\JWTAuth;

final class AuthService
{
    public function __construct(
        private UserClient $userClient,
    ) {}

    public function login(LoginRequest $loginRequest): ?string
    {
        if (
            $token = JWTAuth::attempt([
                'email' => $loginRequest->email,
                'password' => $loginRequest->password,
            ], $loginRequest->rememberMe)
        ) {
            return $token;
        }

        return null;
    }

    /**
     * @throws ValidationException
     * @throws HttpException
     */
    public function getOne(string $email, string $password): User
    {
        $userType = $this->userClient->getOne($email, $password);

        return new User((array) $userType);
    }

    /**
     * @throws ValidationException
     * @throws HttpException
     */
    public function register(RegisterRequest $registerRequest): User
    {
        $userType = $this->userClient->register((array) $registerRequest);

        return new User((array) $userType);
    }

    /**
     * @throws HttpException
     */
    public function confirm(string $hash): User
    {
        $userType = $this->userClient->confirm([
            'hash' => $hash,
        ]);

        return new User((array) $userType);
    }

    /**
     * @throws ValidationException
     * @throws HttpException
     */
    public function forgotPassword(ForgotPasswordRequest $forgotPasswordRequest): User
    {
        $userType = $this->userClient->forgotPassword((array) $forgotPasswordRequest);

        return new User((array) $userType);
    }

    /**
     * @throws ValidationException
     * @throws HttpException
     */
    public function resetPassword(ResetPasswordRequest $resetPasswordRequest): User
    {
        $userType = $this->userClient->forgotPassword((array) $resetPasswordRequest);

        return new User((array) $userType);
    }
}
