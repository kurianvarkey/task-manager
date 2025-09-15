<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\TagDTO;
use App\Helpers\Response\AppResponse;
use App\Http\Requests\TagRequest;
use App\Http\Resources\Tag\TagCollection;
use App\Http\Resources\Tag\TagResource;
use App\Services\TagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class TagController extends Controller
{
    /**
     * Contructor
     */
    public function __construct(
        public TagService $tagService
    ) {}

    /**
     * Store a newly create resource in storage.
     */
    public function store(TagRequest $request): JsonResponse
    {
        $dto = TagDTO::fromArray($request->validated(), 'POST');

        return AppResponse::sendOk(
            data: new TagResource(
                $this->tagService->store($dto)
            ),
            statusCode: Response::HTTP_CREATED
        );
    }

    /**
     * Display a listing of the resource.
     */
    public function index(TagRequest $request): JsonResponse
    {
        return AppResponse::sendOk(
            data: new TagCollection(
                $this->tagService->list(
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
            data: new TagResource(
                $this->tagService->find((int) $id)
            )
        );
    }

    /**
     * Update a specific resource.
     */
    public function update(int|string $id, TagRequest $request): JsonResponse
    {
        $dto = TagDTO::fromArray($request->validated(), $request->getHttpMethod());

        return AppResponse::sendOk(
            data: new TagResource(
                $this->tagService->update(
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
        $this->tagService->delete((int) $id);

        return AppResponse::sendOk(
            data: null,
            statusCode: Response::HTTP_NO_CONTENT,
        );
    }
}
