<?php

namespace TomatoPHP\FilamentApi\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 *  Class APIResponse
 * @package App\Helpers
 * @property static \Illuminate\Http\JsonResponse success($data, $message, $code)
 * @property static \Illuminate\Http\JsonResponse error($message, $code)
 */
class APIResponse
{
    /**
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success(mixed $data=[], string $message = 'OK',int $code = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error(string $message = 'Error',int $code = 400): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $code);
    }

}
