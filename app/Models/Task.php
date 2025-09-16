<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Traits\Auditable;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Task extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'assigned_to',
        'version',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $attributes = [
        'status' => TaskStatus::Pending,
        'priority' => TaskPriority::Medium,
        'version' => 1,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'metadata' => 'json',
        ];
    }

    /**
     * Get the user that owns the task.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to')->select(['id', 'name', 'email']);
    }

    /**
     * Get the tags for the task.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'task_tags', 'task_id', 'tag_id')->select(['id', 'name', 'color']);
    }

    /**
     * Scope a query to only include tag with a given id.
     */
    #[Scope]
    protected function id(Builder $query, int $id): void
    {
        $query->where('id', $id);
    }

    /**
     * Scope a query to only include tasks with a given status.
     */
    #[Scope]
    protected function status(Builder $query, int|string $status): void
    {
        if (empty($status)) {
            return;
        }

        if (is_string($status)) {
            $status = TaskStatus::fromString($status);
        }

        $query->where('status', $status);
    }

    /**
     * Scope a query to only include tasks with a given priority.
     */
    #[Scope]
    protected function priority(Builder $query, int|string $priority): void
    {
        if (empty($priority)) {
            return;
        }

        if (is_string($priority)) {
            $priority = TaskPriority::fromString($priority);
        }

        $query->where('priority', $priority);
    }

    /**
     * Scope a query to only include tasks with a given assigned_to.
     */
    #[Scope]
    protected function assignedTo(Builder $query, int $assignedTo): void
    {
        $query->where('assigned_to', $assignedTo);
    }

    /**
     * Scope a query to only include tasks with a given due_date_range.
     */
    #[Scope]
    protected function dueDateRange(Builder $query, string $datesString): void
    {
        if (empty($datesString)) {
            return;
        }

        $dates = explode(',', $datesString);
        $dates = array_map('trim', explode(',', $datesString));

        $startDate = null;
        $endDate = null;

        try {
            $startDate = Carbon::parse($dates[0]);
            if (isset($dates[1]) && trim($dates[1]) !== '') {
                $endDate = Carbon::parse($dates[1]);
            }

            $endDate = $endDate ?? $startDate;

            if ($endDate->lessThan($startDate)) {
                $endDate = $startDate;
            }
        } catch (Exception $e) {
            // Ignore the exception, and don't apply the scope
            return;
        }

        $query->whereBetween('due_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include tasks with a given tags.
     */
    #[Scope]
    protected function tagsIn(Builder $query, string $tags): void
    {
        if (empty($tags)) {
            return;
        }

        $tagIds = array_map('trim', explode(',', $tags));

        if (empty($tagIds)) {
            return;
        }

        $query->whereHas('tags', function (Builder $query) use ($tagIds) {
            $query->whereIn('tags.id', $tagIds);
        });
    }

    /**
     * Scope a query to only include tasks with a keyword.
     */
    #[Scope]
    protected function keyword(Builder $query, string $keyword): void
    {
        if (empty($keyword)) {
            return;
        }

        // full text search for mysql and pgsql. sqlite doesn't support fulltext
        $driver = DB::connection()->getDriverName();
        match ($driver) {
            'mysql', 'pgsql' => $query->whereFullText(['title', 'description'], $keyword),
            default => $query->where('title', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%"),
        };
    }
}
