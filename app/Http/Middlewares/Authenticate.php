<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Helpers\Response\AppResponse;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
     * The number of minutes to cache the user.
     */
    private const CACHE_USER_IN_MINUTES = 10;

    /**
     * Handle an incoming request and validate the API key.
     *
     * @param  Request  $request  The incoming request.
     * @param  Closure  $next  The next middleware.
     * @return Response The response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = trim($request->bearerToken() ?? '', '"');
        if ($apiKey) {
            // we will cache for n minutes
            $user = Cache::remember($apiKey, now()->addMinutes(self::CACHE_USER_IN_MINUTES), function () use ($apiKey) {
                return User::apiKey($apiKey)->first();
            });

            if ($user) {
                Auth::setUser($user);

                return $next($request);
            }
        }

        return AppResponse::sendError(
            statusCode: Response::HTTP_UNAUTHORIZED,
            errorMessages: 'Unauthorized attempt',
            errorCode: Response::HTTP_UNAUTHORIZED
        );
    }
}
