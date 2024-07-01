<?php

namespace App\Http\Controllers;

use App\Responses\AuthResponse;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class AuthController extends BaseController
{
    public function __construct(protected AuthService $service)
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        $token = $this->service->login($credentials);

        if (!$token)
        {
            return AuthResponse::forbidden();
        }

        $user = $this->service->getUser();

        return AuthResponse::logined($token, $user);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $token = $this->service->register(
            $request->name,
            $request->email,
            $request->password,
        );

        $user = $this->service->getUser();

        return AuthResponse::registred($token, $user);
    }

    public function logout()
    {
        $this->service->logout();

        return AuthResponse::noContent();
    }

    public function refresh()
    {
        $token =  $this->service->refreshToken();

        $user = $this->service->getUser();

        return AuthResponse::logined($token, $user);
    }
}
