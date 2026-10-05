<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Team;
use App\Models\User;

class TeamAccessService
{
    public function canViewTeam(User $user, Team $team): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->isMember($user, $team);
    }

    public function canManageAssignments(User $user, Team $team): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isInstructor() && $this->isMember($user, $team);
    }

    public function canManageStudents(User $user, Team $team): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isInstructor() && $this->isMember($user, $team);
    }

    public function canPostOnGeneral(User $user, Team $team): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isInstructor() && $this->isMember($user, $team);
    }

    public function canReplyOnGeneral(User $user, Team $team): bool
    {
        return $this->canViewTeam($user, $team);
    }

    public function canSubmit(User $user, Assignment $assignment): bool
    {
        if (! $user->isStudent()) {
            return false;
        }

        return $this->isMember($user, $assignment->team);
    }

    public function canReview(User $user, Assignment $assignment): bool
    {
        return $this->canManageAssignments($user, $assignment->team);
    }

    public function isMember(User $user, Team $team): bool
    {
        if ($user->relationLoaded('teams')) {
            return $user->teams->contains('id', $team->id);
        }

        return $user->teams()->where('teams.id', $team->id)->exists();
    }
}
