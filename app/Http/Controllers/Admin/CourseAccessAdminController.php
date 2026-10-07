<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Team;
use App\Models\User;
use App\Services\CourseAccessNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseAccessAdminController extends Controller
{
    public function __construct(
        private CourseAccessNotifier $accessNotifier,
    ) {}

    public function update(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'team_ids' => ['nullable', 'array'],
            'team_ids.*' => ['integer', 'exists:teams,id'],
        ]);

        $userIds = collect($data['user_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        // Never enroll admins via this UI (they already have full access).
        $userIds = User::query()
            ->whereIn('id', $userIds)
            ->where('role', '!=', UserRole::Admin)
            ->pluck('id');

        $teamIds = collect($data['team_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $beforeUsers = $course->enrollments()->pluck('user_id');
        $beforeTeams = $course->teams()->pluck('teams.id');

        Enrollment::query()->where('course_id', $course->id)->whereNotIn('user_id', $userIds)->delete();
        foreach ($userIds as $userId) {
            Enrollment::query()->firstOrCreate([
                'user_id' => $userId,
                'course_id' => $course->id,
            ]);
        }

        $course->teams()->sync($teamIds->all());

        $addedUsers = $userIds->diff($beforeUsers);
        User::query()->whereIn('id', $addedUsers)->get()->each(
            fn (User $user) => $this->accessNotifier->notifyUser($user, $course, 'granted')
        );

        $removedUsers = $beforeUsers->diff($userIds);
        User::query()->whereIn('id', $removedUsers)->get()->each(
            fn (User $user) => $this->accessNotifier->notifyUserRevoked($user, $course, 'revoked')
        );

        $addedTeams = $teamIds->diff($beforeTeams);
        $this->accessNotifier->notifyTeamsGrantedCourse($course, $addedTeams);

        $removedTeams = $beforeTeams->diff($teamIds);
        $this->accessNotifier->notifyTeamsRevokedCourse($course, $removedTeams);

        return back()->with('status', __('Course access updated.'));
    }
}
