<?php

namespace App\Support;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
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

    public static function fromThrowable(Throwable $e): JsonResponse|Response
    {
        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        }

        if ($e instanceof ValidationException) {
            return self::response($e->getMessage(), $e->status, $e->errors());
        }

        $status = match (true) {
            $e instanceof HttpExceptionInterface => $e->getStatusCode(),
            $e instanceof AuthenticationException => 401,
            $e instanceof AuthorizationException => 403,
            $e instanceof ModelNotFoundException, $e instanceof RecordsNotFoundException => 404,
            $e instanceof TokenMismatchException => 419,
            default => 500,
        };
        $message = match ($status) {
            401 => 'Sign in to continue.',
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
