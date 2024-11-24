<?php

declare(strict_types=1);

namespace App\Providers;

use App\Clients\User\UserClient;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class ApiGetwayUserProvider implements UserProvider
{
    protected AuthService $authService;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->authService = new AuthService(
            new UserClient(),
        );
    }

    public function retrieveById(mixed $identifier): ?Authenticatable
    {
        $user = Redis::get($identifier->getAuthIdentifier());

        return $this->getUser($user);
    }

    /**
     * @param string $token
     */
    public function retrieveByToken(mixed $identifier, $token): ?Authenticatable
    {
        $user = $this->getUser(
            Redis::get($identifier->getAuthIdentifier())
        );

        return $user && $user->getRememberToken() && hash_equals($user->getRememberToken(), $token)
                    ? $user : null;
    }

    /**
     * @param  string  $token
     */
    public function updateRememberToken(Authenticatable $user, $token): void
    {
        $cred = Redis::get($user->getAuthIdentifier());
        $cred->setRememberToken($token);
        Redis::put($user->getAuthIdentifier(), $cred);
    }

    /**
     * Здесь нужно просто найти пользователя, от Auth::attempt
     *
     * @throws ValidationException
     * @throws HttpException
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (empty($credentials)) {
            return null;
        }

        return $this->authService->getOne(
            $credentials['email'],
            $credentials['password'],
        );
    }

    /** @deprecated */
    protected function getGenericUser(mixed $user): ?GenericUser
    {
        if (! is_null($user)) {
            return new GenericUser((array) $user);
        }
    }

    protected function getUser(mixed $user): ?User
    {
        if (! is_null($user)) {
            return new User((array) $user);
        }
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        return $user->getAuthIdentifier() === $credentials['email'];
    }
}
