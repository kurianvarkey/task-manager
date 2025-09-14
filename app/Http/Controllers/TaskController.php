<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\Response\AppResponse;
use App\Http\Requests\TaskRequest;
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
        return AppResponse::sendOk(
            data: new TaskResource(
                $this->taskService->store($request->validated())
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
        return AppResponse::sendOk(
            data: new TaskResource(
                $this->taskService->update(
                    (int) $id,
                    $request->validated()
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
