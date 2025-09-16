<?php

/**
 * App response
 * Code credits from Net4Ideas (http://www.net4ideas.com)
 *
 * @version  1.0.0
 *
 * @author   K V P <kurianvarkey@yahoo.com>
 *
 * @link     http://www.net4ideas.com
 */

declare(strict_types=1);

namespace App\Helpers\Response;

use App\Helpers\Validator\ValidationErrorFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class AppResponse
{
    /**
     * Get default headers
     */
    public static function getDefaultHeaders(bool $isAllowOrigin = true): array
    {
        return array_filter([
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Origin' => $isAllowOrigin ? '*' : null,
            'Access-Control-Allow-Headers' => self::getAllowHeaders(),
        ]);
    }

    /**
     * Get allow headers
     */
    public static function getAllowHeaders(): string
    {
        return 'Content-Type, Cache-Control, Origin, Accept, Authorization';
    }

    /**
     * Send Ok response
     */
    public static function sendOk($data, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        $wrappedData = $data instanceof JsonResource && isset($data::$wrap) && $data::$wrap != 'data' ? [$data::$wrap => $data] : $data;

        $responseData = [
            'status' => ($statusCode == Response::HTTP_OK || $statusCode == Response::HTTP_CREATED ? 'success' : 'failed'),
            'data' => $wrappedData,
        ];

        return response()
            ->json($responseData, $statusCode);
    }

    /**
     * Send error response
     */
    public static function sendError(
        int|string $statusCode = Response::HTTP_BAD_REQUEST,
        array|string|null $errorMessages = null,
        int|string|null $errorCode = null,
        bool $validationError = false
    ): JsonResponse {

        if ($validationError) {
            $errors = ValidationErrorFormatter::formatValidationErrors($errorMessages);
            $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
        } else {
            $errors = [ValidationErrorFormatter::formatError(message: $errorMessages, errorCode: $errorCode)];
        }

        if ($statusCode > 1000) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return response()->json([
            'status' => 'failed',
            'errors' => $errors,
        ], $statusCode);
    }

    /**
     * Send Throttle response
     */
    public static function sendThrottle(string $errorMessage, array $headers = []): JsonResponse
    {
        return response()->json([
            'status' => 'failed',
            'errors' => [
                ValidationErrorFormatter::formatError($errorMessage),
            ],
        ], Response::HTTP_TOO_MANY_REQUESTS)
            ->withHeaders(array_merge(
                $headers,
                self::getDefaultHeaders()
            ));
    }
}
