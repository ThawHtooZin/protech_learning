<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\User;
use App\Notifications\CourseAccessGrantedNotification;
use App\Notifications\CourseAccessRevokedNotification;
use Illuminate\Support\Collection;

class CourseAccessNotifier
{
    public function notifyUser(User $user, Course $course, string $reason = 'granted'): void
    {
        if ($user->isAdmin()) {
            return;
        }

        $user->notify(new CourseAccessGrantedNotification($course, $reason));
    }

    public function notifyUserRevoked(User $user, Course $course, string $reason = 'revoked'): void
    {
        if ($user->isAdmin()) {
            return;
        }

        $user->notify(new CourseAccessRevokedNotification($course, $reason));
    }

    /**
     * @param  Collection<int, int>|array<int, int>  $courseIds
     */
    public function notifyUserForNewCourses(User $user, Collection|array $courseIds, string $reason = 'granted'): void
    {
        $ids = collect($courseIds)->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return;
        }

        Course::query()->whereIn('id', $ids)->get()->each(
            fn (Course $course) => $this->notifyUser($user, $course, $reason)
        );
    }

    /**
     * @param  Collection<int, int>|array<int, int>  $teamIds
     */
    public function notifyTeamsGrantedCourse(Course $course, Collection|array $teamIds): void
    {
        $this->usersOnTeams($teamIds)->each(
            fn (User $user) => $this->notifyUser($user, $course, 'team_granted')
        );
    }

    /**
     * @param  Collection<int, int>|array<int, int>  $teamIds
     */
    public function notifyTeamsRevokedCourse(Course $course, Collection|array $teamIds): void
    {
        $this->usersOnTeams($teamIds)->each(
            fn (User $user) => $this->notifyUserRevoked($user, $course, 'team_revoked')
        );
    }

    /**
     * @param  Collection<int, int>|array<int, int>  $teamIds
     * @return Collection<int, User>
     */
    private function usersOnTeams(Collection|array $teamIds): Collection
    {
        $ids = collect($teamIds)->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        return User::query()
            ->whereNotNull('approved_at')
            ->where('role', '!=', UserRole::Admin)
            ->whereHas('teams', fn ($q) => $q->whereIn('teams.id', $ids))
            ->get();
    }
}
