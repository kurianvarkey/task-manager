<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Enums\OperationType;
use App\Models\TaskLog;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait Auditable
{
    /**
     * Boot the Auditable trait for a model.
     */
    public static function bootAuditable()
    {
        static::created(function ($model) {
            (new TaskLog)->create([
                'operation_type' => OperationType::Created,
                'task_id' => $model->id,
                'changes' => $model->toArray(),
                'created_by' => auth()->id(),
            ]);
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();

            // Log only significant changes. When restoring, the deleted_at field is set
            if (empty($changes) || array_key_exists('deleted_at', $changes)) {
                return;
            }

            (new TaskLog)->create([
                'operation_type' => OperationType::Updated,
                'task_id' => $model->id,
                'changes' => $changes,
                'created_by' => auth()->id(),
            ]);
        });

        static::deleted(function ($model) {
            (new TaskLog)->create([
                'operation_type' => OperationType::Deleted,
                'task_id' => $model->id,
                'created_by' => auth()->id(),
            ]);
        });

        static::restored(function ($model) {
            (new TaskLog)->create([
                'operation_type' => OperationType::Restored,
                'task_id' => $model->id,
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Get logs
     */
    public function logs(): HasMany
    {
        return $this->hasMany(TaskLog::class);
    }
}
