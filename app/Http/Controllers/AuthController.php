<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmQuery;
use App\Http\Requests\ForgotPasswordForm;
use App\Http\Requests\LoginForm;
use App\Http\Requests\RegisterForm;
use App\Http\Requests\ResetPasswordForm;
use App\Http\Responses\TokenResource;
use App\Http\Responses\UserResource;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $service,
    ) {}

    /**
     * @throws ValidationException
     * @throws HttpException
     */
    public function get(int $id, string $message): TokenResource
    {
        $token = "$id: $message";

        return new TokenResource($token);
    }

    /**
     * @throws ValidationException
     * @throws HttpException
     */
    public function login(LoginForm $loginForm): TokenResource
    {
        $token = $this->service->login($loginForm); // Тут мы получим успешный ответ от сервиса

        return new TokenResource($token);
    }

    /**
     * @throws ValidationException
     * @throws HttpException
     */
    public function register(RegisterForm $registerForm): UserResource
    {
        $user = $this->service->register($registerForm); // Тут мы получим успешный ответ от сервиса

        return new UserResource(
            id: $user->id,
            status: $user->status,
            email: $user->email,
            phone: $user->phone,
            createdAt: $user->createdAt,
            updatedAt: $user->updatedAt,
            roles: $user->roles,
        );
    }

    /**
     * @throws HttpException
     */
    public function confirm(ConfirmQuery $query): UserResource
    {
        $user = $this->service->confirm($query->hash);

        return new UserResource(
            id: $user->id,
            status: $user->status,
            email: $user->email,
            phone: $user->phone,
            createdAt: $user->createdAt,
            updatedAt: $user->updatedAt,
            roles: $user->roles,
        );
    }

    /**
     * @throws ValidationException
     * @throws HttpException
     */
    public function forgotPassword(ForgotPasswordForm $forgotPasswordForm): UserResource
    {
        $user = $this->service->forgotPassword($forgotPasswordForm);

        return new UserResource(
            id: $user->id,
            status: $user->status,
            email: $user->email,
            phone: $user->phone,
            createdAt: $user->createdAt,
            updatedAt: $user->updatedAt,
            roles: $user->roles,
        );
    }

    /**
     * @throws ValidationException
     * @throws HttpException
     */
    public function resetPassword(ResetPasswordForm $resetPasswordForm): UserResource
    {
        $user = $this->service->resetPassword($resetPasswordForm);

        return new UserResource(
            id: $user->id,
            status: $user->status,
            email: $user->email,
            phone: $user->phone,
            createdAt: $user->createdAt,
            updatedAt: $user->updatedAt,
            roles: $user->roles,
        );
    }

    public function validateToken(): UserResource
    {
        return new UserResource(
            id: Auth::getUser()->id,
            status: Auth::getUser()->status,
            email: Auth::getUser()->email,
            phone: Auth::getUser()->phone,
            createdAt: Auth::getUser()->createdAt,
            updatedAt: Auth::getUser()->updatedAt,
            roles: Auth::getUser()->roles,
        );
    }
}
