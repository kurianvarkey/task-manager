<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\SignupDTO;
use App\Helpers\Response\AppResponse;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Http\Resources\User\LoginResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

final class AuthController extends Controller
{
    /**
     * Contructor
     */
    public function __construct(
        public AuthService $authService
    ) {}

    /**
     * Signup a new user
     */
    public function signup(SignupRequest $request): JsonResponse
    {
        $dto = SignupDTO::fromArray($request->validated());

        // Validate and store the user
        $user = $this->authService->createUser($dto);

        return AppResponse::sendOk(
            data: [
                'email' => $user->email,
                'user_created' => true,
            ]
        );
    }

    /**
     * Login a user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        return AppResponse::sendOk(
            data: new LoginResource(
                $this->authService->login(
                    $request->safe()->email,
                    $request->safe()->password
                )
            )
        );
    }
}
