<?php

declare(strict_types=1);

namespace App\Services;

use App\Clients\User\UserClient;
use App\Http\Requests\ForgotPasswordForm;
use App\Http\Requests\LoginForm;
use App\Http\Requests\RegisterForm;
use App\Http\Requests\ResetPasswordForm;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\Facades\JWTAuth;

final class AuthService
{
    public function __construct(
        private UserClient $userClient,
    ) {}

    public function login(LoginForm $loginForm): ?string
    {
        if (
            $token = JWTAuth::attempt([
                'email' => $loginForm->email,
                'password' => $loginForm->password,
            ], $loginForm->rememberMe)
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
    public function register(RegisterForm $registerForm): User
    {
        $userType = $this->userClient->register((array) $registerForm);

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
    public function forgotPassword(ForgotPasswordForm $forgotPasswordFrom): User
    {
        $userType = $this->userClient->forgotPassword((array) $forgotPasswordFrom);

        return new User((array) $userType);
    }

    /**
     * @throws ValidationException
     * @throws HttpException
     */
    public function resetPassword(ResetPasswordForm $resetPasswordForm): User
    {
        $userType = $this->userClient->forgotPassword((array) $resetPasswordForm);

        return new User((array) $userType);
    }
}
