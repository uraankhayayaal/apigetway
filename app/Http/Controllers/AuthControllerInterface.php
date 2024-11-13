<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Info(title: "2child-back-auth", version: "0.1")]
#[OA\Server('http://localhost:8000', 'Локальный сервер')]
#[OA\Tag(name: "Auth", description: "Авторизация и упраление JWT-токеном")]
interface AuthControllerInterface
{
    #[OA\Post(
        path: '/api/auth/login',
        description: 'Авторизация',
        summary: 'Получение JWT-токена для авторизованного пользователя',
        tags: ['Auth']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            default: '{
                "email": "uraankhayayaal@gmail.com",
                "password": "123456"
            }'
        )
    )]
    #[OA\Response(
        response: "200",
        description: "Успешный ответ",
        content: new OA\JsonContent(
            default: '{
                "status": "success",
                "data": {
                    "user": {
                        "id": 1,
                        "name": "Ayaal",
                        "email": "uraankhayayaal@gmail.com",
                        "email_verified_at": null,
                        "created_at": "2024-07-01T08:30:53.000000Z",
                        "updated_at": "2024-07-01T08:30:53.000000Z"
                    },
                    "authorisation": {
                        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNzE5ODI0NjE1LCJleHAiOjE3MTk4MjgyMTUsIm5iZiI6MTcxOTgyNDYxNSwianRpIjoiYkNSUDVMcTdPbDNBdXRTQSIsInN1YiI6IjEiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.pdI7Iv-lkycpWVqcZlFbpALhUTuabm_pfgF2TNQDgV8",
                        "type": "bearer"
                    }
                }
            }'
        )
    )]
    public function login(Request $request);

    #[OA\Post(
        path: '/api/auth/register',
        description: 'Регистрация',
        summary: 'Добавление нового пользователя и получение JWT-токена',
        tags: ['Auth']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            default: '{
                "name": "Ayaal",
                "email": "uraankhayayaal@gmail.com",
                "password": "123456"
            }'
        )
    )]
    #[OA\Response(
        response: "201",
        description: "Успешный ответ",
        content: new OA\JsonContent(
            default: '{
                "status": "success",
                "data":{
                    "user":{
                        "name": "Ayaal",
                        "email": "uraankhayayaal@gmail.com",
                        "updated_at": "2024-07-01T08:30:53.000000Z",
                        "created_at": "2024-07-01T08:30:53.000000Z",
                        "id": 1
                    },
                    "authorisation":{
                        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYXBpL3JlZ2lzdGVyIiwiaWF0IjoxNzE5ODIyNjUzLCJleHAiOjE3MTk4MjYyNTMsIm5iZiI6MTcxOTgyMjY1MywianRpIjoia1cySVZ0Zjg1VXM1YlN4ZCIsInN1YiI6IjEiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.gU6goAM2V6Y0D1WIcHiX9pi9IVRvanNvh6pC3Tf5Vnw",
                        "type": "bearer"
                    }
                }
            }'
        )
    )]
    public function register(Request $request);

    #[OA\Post(
        path: "/api/auth/logout",
        description: "Выход из системы",
        summary: "Деактуализация JWT-токена",
        tags: ["Auth"],
        security: [["jwt" => []]],
    )]
    #[OA\Response(
        response: "204",
        description: "Успешный ответ"
    )]
    public function logout();

    #[OA\Post(
        path: "/api/auth/refresh",
        description: "Обновление токена",
        summary: "Перевыпуск нового JWT-токена",
        tags: ["Auth"],
        security: [["jwt" => []]],
    )]
    #[OA\Response(
        response: "200",
        description: "Успешный ответ",
        content: new OA\JsonContent(
            default: '{
                "status": "success",
                "data":{
                    "user":{
                        "name": "Ayaal",
                        "email": "uraankhayayaal@gmail.com",
                        "updated_at": "2024-07-01T08:30:53.000000Z",
                        "created_at": "2024-07-01T08:30:53.000000Z",
                        "id": 1
                    },
                    "authorisation":{
                        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYXBpL3JlZ2lzdGVyIiwiaWF0IjoxNzE5ODIyNjUzLCJleHAiOjE3MTk4MjYyNTMsIm5iZiI6MTcxOTgyMjY1MywianRpIjoia1cySVZ0Zjg1VXM1YlN4ZCIsInN1YiI6IjEiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.gU6goAM2V6Y0D1WIcHiX9pi9IVRvanNvh6pC3Tf5Vnw",
                        "type": "bearer"
                    }
                }
            }'
        )
    )]
    public function refresh();
}