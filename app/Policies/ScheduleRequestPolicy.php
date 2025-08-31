<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ScheduleRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class ScheduleRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Super admin bisa melihat semua data
        if ($user->hasRole('super_admin')) {
            return $user->can('view_any_schedule::request');
        }

        // User lain hanya bisa melihat data sesuai divisinya
        return $user->can('view_any_schedule::request');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ScheduleRequest $scheduleRequest): bool
    {
        // Super admin bisa melihat semua data
        if ($user->hasRole('super_admin')) {
            return $user->can('view_schedule::request');
        }

        // User lain hanya bisa melihat data sesuai divisinya
        if ($user->supervisor) {
            $userDivisionId = $user->supervisor->division_id;
            $requestHasUserDivision = $scheduleRequest->requester->supervisor && 
                $scheduleRequest->requester->supervisor->division_id === $userDivisionId;
            
            return $user->can('view_schedule::request') && $requestHasUserDivision;
        }

        return $user->can('view_schedule::request');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_schedule::request');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ScheduleRequest $scheduleRequest): bool
    {
        return $user->can('update_schedule::request');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ScheduleRequest $scheduleRequest): bool
    {
        return $user->can('delete_schedule::request');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_schedule::request');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, ScheduleRequest $scheduleRequest): bool
    {
        return $user->can('force_delete_schedule::request');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_schedule::request');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, ScheduleRequest $scheduleRequest): bool
    {
        return $user->can('restore_schedule::request');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_schedule::request');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, ScheduleRequest $scheduleRequest): bool
    {
        return $user->can('replicate_schedule::request');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_schedule::request');
    }
}
