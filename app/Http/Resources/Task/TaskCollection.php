<?php

declare(strict_types=1);

namespace App\Http\Resources\Task;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TaskCollection extends ResourceCollection
{
    protected array $pagination;

    /**
     * Preparing the pagination array
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        $this->pagination = [
            'total' => $resource?->total() ?? 0,
            'per_page' => $resource?->perPage() ?? 0,
            'current_page' => $resource?->currentPage() ?? 0,
            'last_page' => $resource?->lastPage() ?? 0,
            'from' => $resource?->firstItem() ?? 0,
            'to' => $resource?->lastItem() ?? 0,
        ];

        $resource = $resource->getCollection(); // Necessary to remove meta and links

        parent::__construct($resource);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'meta' => $this->pagination,
            'results' => $this->collection,
        ];
    }
}
