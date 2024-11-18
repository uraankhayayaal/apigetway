<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\AuthService;
use Closure;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Cache;

class ApiGetwayUserProvider implements UserProvider
{
    protected Cache $cache;

    protected AuthService $authService;

    public function __construct(Cache $cache, AuthService $authService)
    {
        $this->cache = $cache;
        $this->authService = $authService;
    }

    public function retrieveById(mixed $identifier): ?Authenticatable
    {
        $user = $this->cache->get($identifier->getAuthIdentifier());

        return $this->getGenericUser($user);
    }

    /**
     * @param  string  $token
     */
    public function retrieveByToken(mixed $identifier, $token): ?Authenticatable
    {
        $user = $this->getGenericUser(
            $this->cache->get($identifier->getAuthIdentifier())
        );

        return $user && $user->getRememberToken() && hash_equals($user->getRememberToken(), $token)
                    ? $user : null;
    }

    /**
     * @param  string  $token
     */
    public function updateRememberToken(Authenticatable $user, $token): void
    {
        $cred = $this->cache->get($user->getAuthIdentifier());
        $cred->setRememberToken($token);
        $this->cache->put($user->getAuthIdentifier(), $cred);
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (empty($credentials)) {
            return;
        }

        $user = $this->authService->getOne($credentials['email'], $credentials['password']); // TODO: is there are safe?

        return $this->getGenericUser($user);
    }

    protected function getGenericUser(mixed $user): ?GenericUser
    {
        if (! is_null($user)) {
            return new GenericUser((array) $user);
        }
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $user = $this->authService->getOne($credentials['email'], $credentials['password']); // TODO: is there are safe?
        return isset($user->id);
    }
}
