<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

final class TaskService extends ServiceBase
{
    /**
     * Construct the service
     */
    public function __construct(
        public ?Tag $taskModel = null
    ) {
        if ($this->taskModel === null) {
            $this->taskModel = new Tag;
        }
    }

    /**
     * Set sortable fields
     */
    public function sortableFields(): array
    {
        return [
            'name',
        ];
    }

    /**
     * Store new record
     *
     * @param  array  $attributes
     * @return null|Model
     */
    public function store(array $input): ?Tag
    {
        return $this->taskModel->create($input);
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
        $query = $this->taskModel
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
        return $this->taskModel
            ->id($id)
            ->firstOrFail();
    }

    /**
     * Update existing record
     */
    public function update(int $id, array $input): ?Tag
    {
        $record = $this->taskModel
            ->id($id)
            ->firstOrFail();

        $record->fill($input)->save();

        return $record->wasChanged() ? $record->refresh() : $record;
    }

    /**
     * Delete the record by id
     */
    public function delete(int $id): bool
    {
        $record = $this->taskModel
            ->select('id')
            ->id($id)
            ->firstOrFail();

        return $record->delete();
    }
}
