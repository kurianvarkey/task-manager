<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\TagDTO;
use App\Exceptions\GeneralException;
use App\Models\Tag;
use Illuminate\Pagination\LengthAwarePaginator;

final class TagService extends ServiceBase
{
    /**
     * Construct the service
     */
    public function __construct(
        public ?Tag $tagModel = null
    ) {
        if ($this->tagModel === null) {
            $this->tagModel = new Tag;
        }
    }

    /**
     * Set sortable fields
     */
    public function sortableFields(): array
    {
        return [
            'name', 'created_at',
        ];
    }

    /**
     * Store new record
     */
    public function store(TagDTO $dto): ?Tag
    {
        return $this->tagModel->create($dto->toArray());
    }

    /**
     * List the records
     */
    public function list(?array $filters = [], ?int $limit = null, ?string $sortField = null, ?string $sortDirection = null): LengthAwarePaginator
    {
        $limit = ($limit && $limit > 0 && $limit <= self::MAX_PAGINATION_LIMIT) ?
            $limit :
            self::DEFAULT_PAGINATION_LIMIT;

        $sortField = $sortField ?? $this->getSortableField($filters['sort'] ?? null);
        $sortDirection = $sortDirection ?? ($filters['direction'] ?? null);

        // Apply filters
        $query = $this->tagModel
            ->when(! empty($filters['name']), function ($query) use ($filters) {
                $query->name($filters['name']);
            });

        if ($sortField && $sortDirection) {
            $query->orderBy($sortField, $sortDirection);
        }

        return $query->paginate($limit);
    }

    /**
     * Find the record by id
     */
    public function find(int $id): ?Tag
    {
        return $this->tagModel
            ->id($id)
            ->firstOrFail();
    }

    /**
     * Update existing record
     */
    public function update(int $id, TagDTO $dto): ?Tag
    {
        $tag = $this->tagModel
            ->id($id)
            ->firstOrFail();

        $tagData = $dto->toArray();
        if (! empty($tagData)) {
            $tag->fill($tagData)->save();
        }

        return $tag->wasChanged() ? $tag->refresh() : $tag;
    }

    /**
     * Delete the record by id
     */
    public function delete(int $id): bool
    {
        $tag = $this->tagModel
            ->select('id')
            ->id($id)
            ->firstOrFail();

        if ($tag->tasks()->select('id')->exists()) {
            throw new GeneralException('Cannot delete this tag as it is used in tasks');
        }

        return $tag->delete();
    }
}
