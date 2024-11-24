<?php

declare(strict_types=1);

namespace App\Clients\User;

use App\Clients\User\Types\UserType;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;

;

class UserClient
{
    public const DEFAULT_ACCEPT_HEADER = 'application/json';
    public const DEFAULT_CACHE_HEADER = 'no-cache';

    private Client $client;

    private string $host = 'http://mk-web';

    public function __construct()
    {
        $this->init();
    }

    /**
     * @throws ValidationException
     * @throws HttpException
     */
    public function getOne(string $email, string $password): UserType
    {
        $data = $this->post('/user/api-auth/get-one', [
            'email' => $email,
            'password' => $password,
        ]);

        return new UserType(...$data);
    }

    /**
     * @param array<string,mixed> $data
     *
     * @throws ValidationException
     * @throws HttpException
     */
    public function register(array $data): UserType
    {
        $data = $this->post('/user/api-auth/register', $data);

        return new UserType(...$data);
    }

    /**
     * @param array<string,mixed> $params
     *
     * @throws HttpException
     */
    public function confirm(array $params): UserType
    {
        $data = $this->get('/user/api-auth/confirm', $params);

        return new UserType(...$data);
    }

    /**
     * @param array<string,mixed> $data
     *
     * @throws ValidationException
     * @throws HttpException
     */
    public function forgotPassword(array $data): UserType
    {
        $data = $this->post('/user/api-auth/forgot-password', $data);

        return new UserType(...$data);
    }

    /**
     * @param array<string,mixed> $data
     *
     * @throws ValidationException
     * @throws HttpException
     */
    public function resetPassword(array $data): UserType
    {
        $data = $this->post('/user/api-auth/reset-password', $data);

        return new UserType(...$data);
    }

    private function init(): void
    {
        $this->client = new Client();
    }

    /**
     * @param mixed[] $data
     *
     * @throws ValidationException
     * @throws HttpException
     */
    private function post(string $url, array $data)
    {
        try {
            $response = $this->client->request('POST', $this->host . $url, [
                RequestOptions::HEADERS => [
                    'host' => 'localhost',
                ],
                RequestOptions::JSON => $data,
            ]);
        } catch (ClientException $e) {
            if ($e->getCode() === 422) {
                try {
                    $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
                    $validator = Validator::make($data, []);
                    foreach ($errorResponse['data'] as $key => $value) {
                        $validator->errors()->add($key, $value);
                    }
                    throw new ValidationException($validator);
                } catch (RuntimeException $runtimeException) {
                    // Ответ получаем не правильный, скорее всего это входные данные не правильные если все таки получаем 422
                    throw new HttpException(422, $runtimeException->getMessage(), $e);
                }
            }

            throw new HttpException($e->getCode(), $e->getMessage(), $e);
        }

        return json_decode((string) $response->getBody(), true)['data'];
    }

    /**
     * @param array<string,mixed> $params
     *
     * @throws HttpException
     */
    private function get(string $url, ?array $params = [])
    {
        try {
            $response = $this->client->request('GET', $this->host . $url, [
                RequestOptions::HEADERS => [
                    'host' => 'localhost',
                    'Accept' => self::DEFAULT_ACCEPT_HEADER,
                    'Cache-Control' => self::DEFAULT_CACHE_HEADER,
                ],
                RequestOptions::QUERY => $params,
            ]);
        } catch (ClientException $e) {
            throw new HttpException($e->getCode(), $e->getMessage(), $e);
        }

        return json_decode((string) $response->getBody(), true)['data'];
    }
}
