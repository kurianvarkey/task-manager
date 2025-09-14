<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskTag extends Model
{
    use HasFactory;

    protected $table = 'task_tags';

    /**
     * Indicates if the IDs are not auto-incrementing. There is no id column in this model.
     *
     * @var false
     */
    public $incrementing = false;

    /**
     * updated_at column to null
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_id',
        'tag_id',
    ];
}
