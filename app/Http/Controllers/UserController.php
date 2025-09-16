<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\Response\AppResponse;
use App\Http\Requests\UserRequest;
use App\Http\Resources\User\UserCollection;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

final class UserController extends Controller
{
    /**
     * Contructor
     */
    public function __construct(
        public UserService $userService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(UserRequest $request): JsonResponse
    {
        return AppResponse::sendOk(
            data: new UserCollection(
                $this->userService->list(
                    $request->validated(),
                    (int) $request?->limit
                )
            )
        );
    }
}
