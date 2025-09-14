<?php

/**
 * Validation error formatter
 * Code credits from Net4Ideas (http://www.net4ideas.com)
 *
 * @version  1.0.0
 *
 * @author   K V P <kurianvarkey@yahoo.com>
 *
 * @link     http://www.net4ideas.com
 */

declare(strict_types=1);

namespace App\Helpers\Validator;

class ValidationErrorFormatter
{
    /**
     * Format error messages.
     */
    public static function formatValidationErrors(array|string $errorMessages): array
    {
        if (is_string($errorMessages)) {
            return [self::formatError($errorMessages)];
        }

        $errors = [];
        foreach ($errorMessages as $key => $message) {
            $messages = is_array($message) ? $message : [$message];
            foreach ($messages as $messageItem) {
                $errors[] = self::formatError((string) $messageItem, 'validation', $key);
            }
        }

        return $errors;
    }

    /**
     * Format an error message.
     *
     * @param  string  $message  The error message.
     * @param  string  $errorType  The type of error. Default is 'system'.
     * @param  string|null  $errorKey  The key of the error. Default is null.
     * @param  string|null  $errorCode  The code of the error. Default is null.
     * @return array The formatted error.
     */
    public static function formatError(
        string $message,
        string $errorType = 'system',
        ?string $errorKey = null,
        ?int $errorCode = null
    ): array {
        return array_filter([
            'type' => $errorType,
            'key' => $errorKey,
            'code' => $errorCode,
            'message' => $message,
        ]);
    }
}
