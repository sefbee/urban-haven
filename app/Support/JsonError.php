<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class JsonError
{
    /**
     * @param  array<string, array<int, string>>|null  $errors
     */
    public static function response(string $message, int $status, ?array $errors = null): JsonResponse
    {
        $payload = ['message' => $message];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    public static function fromThrowable(Throwable $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return self::response($e->getMessage(), $e->status, $e->errors());
        }

        $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
        $message = match ($status) {
            403 => 'This action is unauthorised.',
            404 => 'The requested resource was not found.',
            419 => 'The page expired. Refresh and try again.',
            429 => 'Too many attempts. Please wait and try again.',
            500 => 'Something went wrong. Please try again.',
            default => $e->getMessage() !== '' ? $e->getMessage() : 'Request failed.',
        };

        return self::response($message, $status);
    }
}
