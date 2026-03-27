<?php

namespace App\Support\Api;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function success(array $data, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'error' => null,
        ], $statusCode);
    }

    /**
     * @param  array<string, mixed>|null  $details
     */
    public static function error(
        string $code,
        string $reasonCode,
        string $message,
        ?array $details = null,
        int $statusCode = 422,
    ): JsonResponse {
        return response()->json([
            'status' => 'error',
            'data' => null,
            'error' => [
                'code' => $code,
                'reasonCode' => $reasonCode,
                'message' => $message,
                'details' => $details,
            ],
        ], $statusCode);
    }
}
