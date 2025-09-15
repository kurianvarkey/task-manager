<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === Role::Admin) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === Role::Admin;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        return $user->id === $task->assigned_to;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        return $user->id === $task->assigned_to;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->assigned_to;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return $user->id === $task->assigned_to;
    }
}
