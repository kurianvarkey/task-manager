<?php

/**
 * App customisation to handle api & error responses
 * Code credits from Net4Ideas (http://www.net4ideas.com)
 *
 * @version  1.0.0
 *
 * @author   K V P <kurianvarkey@yahoo.com>
 *
 * @link     http://www.net4ideas.com
 */

use App\Exceptions\GeneralException;
use App\Helpers\Response\AppResponse;
use App\Http\Middlewares\Cors;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Response;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: '/api',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(Cors::class);

        $middleware->group('api', [
            'throttle:api',
            SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReport([
            GeneralException::class,
        ]);

        $exceptions->render(function (Throwable $exception) {
            if ($exception instanceof ThrottleRequestsException) {
                return AppResponse::sendThrottle($exception->getMessage());
            }

            if ($exception instanceof ValidationException) {
                return AppResponse::sendError(
                    statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
                    errorMessages: $exception->errors() ?? $exception->getMessage(),
                    validationError: true
                );
            }

            $code = $exception->getCode() > 0 ?: Response::HTTP_BAD_REQUEST;
            $statusCode = Response::HTTP_BAD_REQUEST;
            $message = '';

            switch (true) {
                case $exception instanceof ModelNotFoundException:
                    $message = 'No record found for given id';
                    $statusCode = $code = Response::HTTP_NOT_FOUND;
                    break;
                case $exception instanceof NotFoundHttpException:
                    $model = str()->afterLast($exception?->getPrevious()?->getModel(), '\\');
                    $message = $model !== null ? "The $model could not be found. Please check your request or endpoint." : $exception->getMessage();
                    $statusCode = $code = Response::HTTP_NOT_FOUND;
                    break;
                case $exception instanceof MethodNotAllowedHttpException:
                    $message = 'Method Not Allowed. Please check your endpoint and http verb type. ' . $exception->getMessage();
                    break;
                case $exception instanceof InvalidArgumentException:
                case $exception instanceof GeneralException:
                default:
                    $message = $exception->getMessage();
                    $statusCode = $exception->getCode() >= 400 ? $exception->getCode() : $statusCode;
                    break;
            }

            return AppResponse::sendError(
                statusCode: $statusCode ?? Response::HTTP_BAD_REQUEST,
                errorMessages: $message ?? $exception->getMessage(),
                errorCode: $code ?? $exception->getCode()
            );
        });
    })->create();
