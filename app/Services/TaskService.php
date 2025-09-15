<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\TaskDTO;
use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;

final class TaskService extends ServiceBase
{
    /**
     * Construct the service
     */
    public function __construct(
        public ?Task $taskModel = null
    ) {
        if ($this->taskModel === null) {
            $this->taskModel = new Task;
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
     */
    public function store(TaskDTO $dto): ?Task
    {
        // Set enums and default values
        $dto->setDefaults();

        return $this->doDbTransaction(function () use ($dto) {
            $task = $this->taskModel->create($dto->toArray());

            $tags = $dto->tags();
            if ($tags) {
                $task->tags()->attach($tags);
            }

            return $task->loadMissing(['assignedUser', 'tags']);
        });
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
    public function find(int $id): ?Task
    {
        return $this->taskModel
            ->id($id)
            ->firstOrFail();
    }

    /**
     * Update existing record
     */
    public function update(int $id, TaskDTO $dto): ?Task
    {
        // Set enums values
        $dto->setEnums();

        $task = $this->taskModel
            ->id($id)
            ->firstOrFail();

        $task = $this->doDbTransaction(function () use ($task, $dto) {
            $task->fill($dto->toArray())->save();

            $tags = $dto->tags();
            if ($tags) {
                $task->tags()->sync($tags);
            }

            return $task->wasChanged() ? $task->refresh() : $task;
        });

        return $task->loadMissing(['assignedUser', 'tags']);
    }

    /**
     * Delete the record by id
     */
    public function delete(int $id): bool
    {
        $task = $this->taskModel
            ->select('id')
            ->id($id)
            ->firstOrFail();

        $task->tags()->detach();

        return $task->delete();
    }
}
