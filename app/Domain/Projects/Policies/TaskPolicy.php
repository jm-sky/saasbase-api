<?php

namespace App\Domain\Projects\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Projects\Models\Task;

class TaskPolicy
{
    /**
     * Determine whether the user can view the task.
     */
    public function view(User $user, Task $task): bool
    {
        return $this->isRelatedToTask($user, $task);
    }

    /**
     * Determine whether the user can create tasks.
     */
    public function create(User $user): bool
    {
        // Allow all authenticated tenant members to create tasks (customize as needed)
        return true;
    }

    /**
     * Determine whether the user can update the task.
     */
    public function update(User $user, Task $task): bool
    {
        return $this->isRelatedToTask($user, $task);
    }

    /**
     * Determine whether the user can delete the task.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->created_by_id || $user->id === $task->project?->owner_id;
    }

    /**
     * Creator, assignee, or the owning project's owner/member.
     */
    private function isRelatedToTask(User $user, Task $task): bool
    {
        return $user->id === $task->created_by_id
            || $user->id === $task->assignee_id
            || $user->id === $task->project?->owner_id
            || ($task->project?->users->contains($user->id) ?? false);
    }
}
