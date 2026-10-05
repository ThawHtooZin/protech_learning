<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeamAdminController extends Controller
{
    public function show(Team $team): View
    {
        $team->load(['users.profile'])->loadCount('users');

        $instructors = User::query()
            ->with('profile')
            ->where('role', UserRole::Instructor)
            ->orderBy('email')
            ->get();

        $students = User::query()
            ->with('profile')
            ->where('role', UserRole::Student)
            ->orderBy('email')
            ->get();

        $instructorCount = $team->users->filter(fn (User $user) => $user->isInstructor())->count();
        $studentCount = $team->users->filter(fn (User $user) => $user->isStudent())->count();

        return view('admin.teams.show', compact(
            'team',
            'instructors',
            'students',
            'instructorCount',
            'studentCount',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:teams,name'],
        ]);

        $team = Team::query()->create($validated);

        return redirect()
            ->route('admin.teams.show', $team)
            ->with('status', __('Team created.'));
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('teams', 'name')->ignore($team->id)],
        ]);

        $team->update($validated);

        return redirect()
            ->route('admin.teams.show', $team)
            ->with('status', __('Team updated.'));
    }

    public function attachMembers(Request $request, Team $team): RedirectResponse
    {
        $validated = $request->validate([
            'instructor_ids' => ['sometimes', 'array'],
            'instructor_ids.*' => ['integer', Rule::exists('users', 'id')->where('role', UserRole::Instructor->value)],
            'student_ids' => ['sometimes', 'array'],
            'student_ids.*' => ['integer', Rule::exists('users', 'id')->where('role', UserRole::Student->value)],
        ]);

        $memberIds = collect($validated['instructor_ids'] ?? [])
            ->concat($validated['student_ids'] ?? [])
            ->unique()
            ->values()
            ->all();

        if ($memberIds !== []) {
            $team->users()->syncWithoutDetaching($memberIds);
        }

        return redirect()
            ->route('admin.teams.show', $team)
            ->with('status', __('Members added.'));
    }

    public function detachMember(Team $team, User $user): RedirectResponse
    {
        $team->users()->detach($user->id);

        return redirect()
            ->route('admin.teams.show', $team)
            ->with('status', __('Member removed.'));
    }

    public function destroy(Team $team): RedirectResponse
    {
        $team->delete();

        return redirect()
            ->route('admin.users.index', ['tab' => 'teams'])
            ->with('status', __('Team deleted.'));
    }
}
