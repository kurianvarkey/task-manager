<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\TaskDTO;
use App\Helpers\Response\AppResponse;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\Task\TaskCollection;
use App\Http\Resources\Task\TaskResource;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class TaskController extends Controller
{
    /**
     * Contructor
     */
    public function __construct(
        public TaskService $taskService
    ) {}

    /**
     * Store a newly create resource in storage.
     */
    public function store(TaskRequest $request): JsonResponse
    {
        $dto = TaskDTO::fromArray($request->validated(), 'POST');

        return AppResponse::sendOk(
            data: new TaskResource(
                $this->taskService->store($dto)
            ),
            statusCode: Response::HTTP_CREATED
        );
    }

    /**
     * Display a listing of the resource.
     */
    public function index(TaskRequest $request): JsonResponse
    {
        return AppResponse::sendOk(
            data: new TaskCollection(
                $this->taskService->list(
                    $request->validated(),
                    (int) $request?->limit
                )
            )
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(int|string $id): JsonResponse
    {
        return AppResponse::sendOk(
            data: new TaskResource(
                $this->taskService->find((int) $id)
            )
        );
    }

    /**
     * Update a specific resource.
     */
    public function update(int|string $id, TaskRequest $request): JsonResponse
    {
        $dto = TaskDTO::fromArray($request->validated(), $request->getHttpMethod());

        return AppResponse::sendOk(
            data: new TaskResource(
                $this->taskService->update(
                    (int) $id,
                    $dto
                )
            )
        );
    }

    /**
     * Delete a specific resource.     *
     */
    public function destroy(string $id): ?JsonResponse
    {
        $this->taskService->delete((int) $id);

        return AppResponse::sendOk(
            data: null,
            statusCode: Response::HTTP_NO_CONTENT,
        );
    }
}
