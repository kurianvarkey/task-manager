<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Helpers\Response\AppResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    /**
     * Handle an incoming request and set CORS headers.
     *
     * @codeCoverageIgnore
     */
    public function handle(Request $request, Closure $next): Response
    {
        /**
         * Removes the "X-Powered-By" header from the response.
         * This is a common security practice to avoid revealing the underlying technology stack.
         */
        header_remove('X-Powered-By');

        $origin = $request->headers->get('Origin') ?? $request->headers->get('Referer');
        $allowedOrigins = [];
        $isAllowOrigin = empty($origin) || empty($allowedOrigins) || in_array($origin, $allowedOrigins);

        if (! $isAllowOrigin || $request->isMethod('OPTIONS')) {
            return response(
                $isAllowOrigin ? 'OK' : 'Forbidden',
                $isAllowOrigin ? Response::HTTP_OK : Response::HTTP_FORBIDDEN
            )
                ->withHeaders(AppResponse::getDefaultHeaders($isAllowOrigin));
        }

        return $next($request);
    }
}
