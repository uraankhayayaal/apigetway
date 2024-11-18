<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class AuthService
{
    public function __construct(
        private Client $client,
    ) {}

    public function getOne(string $email, string $password): array
    {
        try {
            $response = $this->client->request('POST', 'http://mk-web/user/api-auth/get-one', [
                'headers' => [
                    'host' => 'localhost',
                ],
                RequestOptions::JSON => [
                    'email' => $email,
                    'password' => $password,
                ],
            ]);
        } catch (ClientException $e) {
            if ($e->getCode() === 422) {
                $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
                $validator = Validator::make(['email' => $email, 'password' => $password], []);
                foreach ($errorResponse['data'] as $key => $value) {
                    $validator->errors()->add($key, $value);
                }
                throw new ValidationException($validator);
            }
            throw $e;
        }

        if ($response->getReasonPhrase() !== 'OK') {
            throw new HttpException(404, 'User not found.');
        }

        return json_decode((string) $response->getBody(), true)['data'];
    }

    public function login(LoginRequest $loginRequest): array
    {
        try {
            $response = $this->client->request('POST', 'http://mk-web/user/api-auth/get-one', [
                'headers' => [
                    'host' => 'localhost',
                ],
                RequestOptions::JSON => [
                    'email' => $loginRequest->email,
                    'password' => $loginRequest->password,
                ],
            ]);
        } catch (ClientException $e) {
            if ($e->getCode() === 422) {
                $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
                $validator = Validator::make((array) $loginRequest, []);
                foreach ($errorResponse['data'] as $key => $value) {
                    $validator->errors()->add($key, $value);
                }
                throw new ValidationException($validator);
            }
            throw $e;
        }

        if ($response->getReasonPhrase() !== 'OK') {
            throw new HttpException(404, 'User not found.');
        }

        $user = new User(json_decode((string) $response->getBody(), true)['data']);

        return [
            'accessToken' => $this->generateAccessToken($user, $loginRequest->rememberMe),
            'refreshToken' => $this->generateRefreshToken($user),
            'user' => auth()->user(),
        ];
    }

    public function register(RegisterRequest $registerRequest): User
    {
        try {
            $response = $this->client->request('POST', 'http://mk-web/user/api-auth/register', [
                'headers' => [
                    'host' => 'localhost',
                ],
                RequestOptions::JSON => [
                    'email' => $registerRequest->email,
                    'phone' => $registerRequest->phone,
                    'username' => $registerRequest->username,
                    'password' => $registerRequest->password,
                    'isAgreeMarketing' => $registerRequest->isAgreeMarketing,
                    'isAgreePolicy' => $registerRequest->isAgreePolicy,
                ],
            ]);
        } catch (ClientException $e) {
            if ($e->getCode() === 422) {
                $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
                $validator = Validator::make((array) $registerRequest, []);
                foreach ($errorResponse['data'] as $key => $value) {
                    $validator->errors()->add($key, $value);
                }
                throw new ValidationException($validator);
            }
            throw $e;
        }

        if ($response->getReasonPhrase() !== 'OK') {
            throw new HttpException(404, 'User not found.');
        }

        return new User(json_decode((string) $response->getBody(), true)['data']);
    }

    public function confirm(string $hash): User
    {
        $response = $this->client->request('GET', 'http://mk-web/user/api-auth/confirm?hash=' . $hash);

        if ($response->getReasonPhrase() !== 'OK') {
            throw new HttpException(404, 'User not found.');
        }

        $user = new User(json_decode($response->getBody()->getContents(), true));

        return $user;
    }

    public function forgotPassword(ForgotPasswordRequest $forgotPasswordRequest): User
    {
        $response = $this->client->request('POST', 'http://mk-web/user/api-auth/forgot-password', [
            RequestOptions::JSON => [
                'email' => $forgotPasswordRequest->email,
            ],
        ]);

        if ($response->getReasonPhrase() !== 'OK') {
            throw new HttpException(404, 'User not found.');
        }

        $user = new User(json_decode($response->getBody()->getContents(), true));

        return $user;
    }

    public function resetPassword(ResetPasswordRequest $resetPasswordRequest): User
    {
        $response = $this->client->request('POST', 'http://mk-web/user/api-auth/reset-password', [
            RequestOptions::JSON => [
                'email' => $resetPasswordRequest->passwordResetToken,
                'email' => $resetPasswordRequest->password,
            ],
        ]);

        if ($response->getReasonPhrase() !== 'OK') {
            throw new HttpException(404, 'User not found.');
        }

        $user = new User(json_decode($response->getBody()->getContents(), true));

        return $user;
    }

    public function refreshToken(RefreshTokenRequest $refreshTokenRequest): array
    {
        return [
            'user' => auth()->user(),
            'accessToken' => auth()->refresh,
            'refreshToken' => '',
        ];
    }

    private function generateAccessToken(User $user, bool $rememberMe = false): bool|string
    {
        return Auth::attempt([$user->id], $rememberMe);
    }

    private function generateRefreshToken(User $user): string
    {
        return '';
    }
}
