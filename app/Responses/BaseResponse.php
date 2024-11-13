<?php

namespace App\Responses;

use Illuminate\Http\JsonResponse;

class BaseResponse
{
    static public function json(mixed $data) : JsonResponse
    {
        return response()->json($data);
    }

    static public function deleted(int $count) : JsonResponse
    {
        return self::success(['deleted' => $count]);
    }

    static public function noContent() : JsonResponse
    {
        return response()->json(null, 204);
    }

    static public function created(mixed $data) : JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 201);
    }

    static public function success(mixed $data) : JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    static public function error(string $message, int $code = 500) : JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $code);
    }

    static public function forbidden() : JsonResponse
    {
        return self::error('Unauthorized', 401);
    }
}
