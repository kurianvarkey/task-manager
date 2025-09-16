<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\TaskDTO;
use App\Enums\Role;
use App\Enums\TaskStatus;
use App\Exceptions\GeneralException;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

final class TaskService extends ServiceBase
{
    private User $authUser;

    /**
     * Construct the service
     */
    public function __construct(
        public ?Task $taskModel = null
    ) {
        if ($this->taskModel === null) {
            $this->taskModel = new Task;
        }

        $this->authUser = auth()?->user();
    }

    /**
     * Set sortable fields
     */
    public function sortableFields(): array
    {
        return [
            'title', 'status', 'priority', 'due_date', 'created_at',
        ];
    }

    /**
     * Store new record
     */
    public function store(TaskDTO $dto): ?Task
    {
        // Set enums and default values
        $dto->setDefaults();
        $taskData = $dto->toArray();

        // if the logged in user is a user, assign the task to the user
        if (empty($taskData['assigned_to']) && $this->authUser?->role === Role::User) {
            $taskData['assigned_to'] = $this->authUser->id;
        }

        return $this->doDbTransaction(function () use ($taskData, $dto) {
            $task = $this->taskModel->create($taskData);

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
        $query = $this->taskModel->newQuery();

        if ($this->authUser?->role === Role::User) {
            $query->assignedTo($this->authUser->id);
        }

        // Apply filters
        $this->applyFilters($query, $filters);

        $limit = ($limit && $limit > 0 && $limit <= self::MAX_PAGINATION_LIMIT) ?
            $limit :
            self::DEFAULT_PAGINATION_LIMIT;

        $sortField = $sortField ?? $this->getSortableField($filters['sort'] ?? null);
        $sortDirection = $sortDirection ?? ($filters['direction'] ?? null);

        if ($sortField && $sortDirection) {
            $query->orderBy($sortField, $sortDirection);
        }

        // load relationships
        $query->with(['assignedUser', 'tags']);

        return $query->paginate($limit);
    }

    /**
     * Find the record by id
     */
    public function find(int $id): ?Task
    {
        $task = $this->taskModel->id($id)
            ->with(['assignedUser', 'tags'])
            ->firstOrFail();

        Gate::authorize('view', $task);

        return $task;
    }

    /**
     * Update existing record
     */
    public function update(int $id, TaskDTO $dto): ?Task
    {
        // Set enums values
        $dto->setEnums();
        $taskData = $dto->toArray();
        $httpMethod = $dto->getHttpMethod();

        if ($httpMethod === 'PUT' && empty($taskData['version'])) {
            throw new GeneralException('Version is required');
        }

        $task = $this->taskModel->id($id)->firstOrFail();

        Gate::authorize('update', $task);

        $task = $this->doDbTransaction(function () use ($task, $taskData, $dto, $httpMethod) {
            if ($httpMethod === 'PUT') {
                if ($task->version !== $taskData['version']) {
                    throw new GeneralException('This task has been updated by someone else and there is a newer version of this task exists', Response::HTTP_CONFLICT);
                }
                $taskData['version'] = $task->version + 1;
            }

            if (! empty($taskData)) {
                $task->fill($taskData)->save();
            }

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
            ->select(['id', 'assigned_to'])
            ->id($id)
            ->firstOrFail();

        Gate::authorize('delete', $task);

        // In future, if we need permanent delete, then uncomment following line
        // $task->tags()->detach();

        return $task->delete();
    }

    /**
     * Toggle status
     */
    public function toggleStatus(int $id): Task
    {
        $task = $this->taskModel->id($id)->firstOrFail();

        Gate::authorize('update', $task);

        $task->status = match ($task->status) {
            TaskStatus::Pending => TaskStatus::InProgress,
            TaskStatus::InProgress => TaskStatus::Completed,
            TaskStatus::Completed => TaskStatus::Pending,
        };
        $task->save();

        return $task->loadMissing(['assignedUser', 'tags']);
    }

    /**
     * Restore a soft deleted resource
     */
    public function restore(int $id): Task
    {
        $task = $this->taskModel->withTrashed()->id($id)->firstOrFail();

        Gate::authorize('restore', $task);

        $task->restore();

        return $task->loadMissing(['assignedUser', 'tags']);
    }

    /**
     * Get logs
     */
    public function getLogs(int $id): LengthAwarePaginator
    {
        $task = $this->taskModel->id($id)->firstOrFail();

        Gate::authorize('view', $task);

        return $task->logs()
            ->with(['createdBy'])
            ->orderByDesc('id')
            ->orderByDesc('created_at')
            ->paginate(self::DEFAULT_PAGINATION_LIMIT);
    }

    /**
     * Apply filters
     */
    private function applyFilters(Builder $query, $filters): void
    {
        // filter by status
        $query->when(! empty($filters['status']), function ($query) use ($filters) {
            $query->status($filters['status']);
        })
        // filter by priority
            ->when(! empty($filters['priority']), function ($query) use ($filters) {
                $query->priority($filters['priority']);
            })
        // filter by assigned_to
            ->when(! empty($filters['assigned_to']), function ($query) use ($filters) {
                $query->assignedTo($filters['assigned_to']);
            })
        // filter by due_date_range
            ->when(! empty($filters['due_date_range']), function ($query) use ($filters) {
                $query->where(function ($query) use ($filters) {
                    $query->dueDateRange($filters['due_date_range']);
                });
            })
        // filter by tags
            ->when(! empty($filters['tags']), function ($query) use ($filters) {
                $query->tagsIn($filters['tags']);
            })
        // filter by keywords
            ->when(! empty($filters['keyword']), function ($query) use ($filters) {
                $query->keyword($filters['keyword']);
            })
        // filter by only_deleted (soft deleted) tasks
            ->when(! empty($filters['only_deleted']), function ($query) {
                $query->onlyTrashed();
            });
    }
}
