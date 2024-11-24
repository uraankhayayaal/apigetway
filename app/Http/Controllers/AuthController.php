<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Responses\TokenResponse;
use App\Http\Responses\UserResponse;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    public function login(Request $request): JsonResponse
    {
        $this->validate($request, [
            'email' => 'required|max:255',
            'password' => 'required',
            'rememberMe' => 'boolean',
        ]);

        $loginRequest = new LoginRequest(...$request->all());

        $token = $this->service->login($loginRequest); // Тут мы получим успешный ответ от сервиса

        return response()->json( // TODO: и если здесь будет ошибка, то зановно запрос должен отработать корректно
            new TokenResponse($token)
        );
    }

    /**
     * @throws ValidationException
     * @throws HttpException
     */
    public function register(Request $request): JsonResponse
    {
        $this->validate($request, [
            'username' => 'required|max:255',
            'email' => 'required|max:255',
            'phone' => 'required|max:255',
            'password' => 'required',
            'isAgreeMarketing' => 'required|boolean',
            'isAgreePolicy' => 'required|boolean',
        ]);

        $registerRequest = new RegisterRequest(...$request->all());

        $user = $this->service->register($registerRequest); // Тут мы получим успешный ответ от сервиса

        return response()->json( // TODO: и если здесь будет ошибка, то зановно запрос должен отработать корректно
            new UserResponse(
                id: $user->id,
                status: $user->status,
                email: $user->email,
                phone: $user->phone,
                createdAt: $user->createdAt,
                updatedAt: $user->updatedAt,
                roles: $user->roles,
            )
        );

    }

    /**
     * @throws HttpException
     */
    public function confirm(Request $request): JsonResponse
    {
        $hash = $request->get('hash');
        $user = $this->service->confirm($hash);

        return response()->json(
            new UserResponse(
                id: $user->id,
                status: $user->status,
                email: $user->email,
                phone: $user->phone,
                createdAt: $user->createdAt,
                updatedAt: $user->updatedAt,
                roles: $user->roles,
            )
        );
    }

    /**
     * @throws ValidationException
     * @throws HttpException
     */
    public function forgotPassword(Request $request): UserResponse
    {
        $this->validate($request, [
            'email' => 'required|max:255',
        ]);

        $forgotPasswordRequest = new ForgotPasswordRequest(...$request->all());

        $user = $this->service->forgotPassword($forgotPasswordRequest);

        return new UserResponse(
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
    public function resetPassword(Request $request): UserResponse
    {
        $this->validate($request, [
            'passwordResetToken' => 'required|max:255',
            'password' => 'required',
        ]);

        $resetPasswordRequest = new ResetPasswordRequest(...$request->all());

        $user = $this->service->resetPassword($resetPasswordRequest);

        return new UserResponse(
            id: $user->id,
            status: $user->status,
            email: $user->email,
            phone: $user->phone,
            createdAt: $user->createdAt,
            updatedAt: $user->updatedAt,
            roles: $user->roles,
        );
    }

    public function validateToken(): UserResponse
    {
        return new UserResponse(
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
