<?php

namespace App\Domain\Projects\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Projects\Models\Project;

class ProjectPolicy
{
    /**
     * Determine whether the user can view the project.
     */
    public function view(User $user, Project $project): bool
    {
        // Owner or assigned user can view
        return $user->id === $project->owner_id || $project->users->contains($user->id);
    }

    /**
     * Determine whether the user can create projects.
     */
    public function create(User $user): bool
    {
        // Allow all authenticated users to create projects (customize as needed)
        return true;
    }

    /**
     * Determine whether the user can update the project.
     */
    public function update(User $user, Project $project): bool
    {
        // Owner or assigned user can update
        return $user->id === $project->owner_id || $project->users->contains($user->id);
    }

    /**
     * Determine whether the user can delete the project.
     */
    public function delete(User $user, Project $project): bool
    {
        // Only the owner can delete
        return $user->id === $project->owner_id;
    }
}
