<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'color',
    ];

    /**
     * Get the tasks for the tags.
     */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_tags', 'tag_id', 'task_id');
    }

    /**
     * Scope a query to only include tag with a given name.
     */
    #[Scope]
    protected function name(Builder $query, string $name): void
    {
        $query->where('name', $name);
    }

    /**
     * Scope a query to only include tag with a given id.
     */
    #[Scope]
    protected function id(Builder $query, int $id): void
    {
        $query->where('id', $id);
    }
}
