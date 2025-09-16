<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

final class UserService extends ServiceBase
{
    /**
     * Construct the service
     */
    public function __construct(
        public ?User $userModel = null
    ) {
        if ($this->userModel === null) {
            $this->userModel = new User;
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
        $query = $this->userModel
            ->when(! empty($filters['name']), function ($query) use ($filters) {
                $query->name($filters['name']);
            })->when(! empty($filters['email']), function ($query) use ($filters) {
                $query->email($filters['email']);
            });

        if ($sortField && $sortDirection) {
            $query->orderBy($sortField, $sortDirection);
        }

        return $query->paginate($limit);
    }
}
