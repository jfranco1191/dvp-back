<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponserTrait
{
    /**
     * @param mixed $data
     * @param int $statusCode
     * @param string $message
     * @return JsonResponse
     */
    public function jsonSuccess(mixed $data = null, int $statusCode = 200, string $message = ""): JsonResponse
    {
        if (empty(Response::$statusTexts[$statusCode])) {
            $statusCode = 200;
        }
        if (empty($message)) {
            $message = Response::$statusTexts[$statusCode];
        }

        return response()->json(["success" => true, "message" => $message, "body" => $data], $statusCode);
    }

    /**
     * @param null|array<string, mixed> $data
     * @param int $statusCode
     * @param string $message
     * @return JsonResponse
     */
    public function jsonError(array $data = null, int $statusCode = 500, string $message = ""): JsonResponse
    {
        if (empty(Response::$statusTexts[$statusCode])) {
            $statusCode = 500;
        }
        if (empty($message)) {
            $message = Response::$statusTexts[$statusCode];
        }

        return response()->json(["success" => false, "message" => $message, "body" => $data], $statusCode);
    }
}
