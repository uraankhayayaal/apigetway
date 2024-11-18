<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Responses\TokenResponse;
use App\Http\Responses\UserResponse;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Routing\Controller;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $service,
    ) {}

    /**
     * @throws Illuminate\Validation\ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $this->validate($request, [
            'email' => 'required|max:255',
            'password' => 'required',
            'rememberMe' => 'boolean',
        ]);

        $loginRequest = new LoginRequest(...$request->all());

        $tokens = $this->service->login($loginRequest);

        return response()->json(
            new TokenResponse(...$tokens)
        );
    }

    /**
     * @throws Illuminate\Validation\ValidationException
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

        $user = $this->service->register($registerRequest);

        return response()->json(
            new UserResponse(
                id: $user->id,
                email: $user->email,
                phone: $user->phone,
                createdAt: $user->createdAt,
                roles: $user->roles,
            )
        );

    }

    public function confirm(string $hash): UserResponse
    {
        $user = $this->service->confirm($hash);

        return new UserResponse(
            id: $user->id,
            email: $user->email,
            phone: $user->phone,
            createdAt: $user->createdAt,
            roles: $user->roles,
        );
    }

    /**
     * @throws Illuminate\Validation\ValidationException
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
            email: $user->email,
            phone: $user->phone,
            createdAt: $user->createdAt,
            roles: $user->roles,
        );
    }

    /**
     * @throws Illuminate\Validation\ValidationException
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
            email: $user->email,
            phone: $user->phone,
            createdAt: $user->createdAt,
            roles: $user->roles,
        );
    }

    public function validateToken(): UserResponse
    {
        return new UserResponse(
            id: Auth::getUser()->id,
            email: Auth::getUser()->email,
            phone: Auth::getUser()->phone,
            createdAt: Auth::getUser()->createdAt,
            roles: Auth::getUser()->roles,
        );
    }

    /**
     * @throws Illuminate\Validation\ValidationException
     */
    public function refreshToken(Request $request): TokenResponse
    {
        $this->validate($request, [
            'refreshToken' => 'required|max:255',
        ]);

        $refreshTokenRequest = new RefreshTokenRequest(...$request->all());

        $tokens = $this->service->refreshToken($refreshTokenRequest);

        return new TokenResponse(...$tokens);
    }
}
