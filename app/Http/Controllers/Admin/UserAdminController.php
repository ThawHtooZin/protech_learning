<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserAdminController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with('profile')
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load('profile', 'enrollments');
        $courses = Course::query()->orderBy('title')->get();
        $assignedCourseIds = $user->enrollments()->pluck('course_id')->all();

        return view('admin.users.show', compact('user', 'courses', 'assignedCourseIds'));
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        if ($user->approved_at) {
            return back()->with('status', __('User already approved.'));
        }

        $user->forceFill([
            'approved_at' => now(),
            'approved_by_user_id' => $request->user()->id,
        ])->save();

        return back()->with('status', __('User approved.'));
    }

    public function revoke(User $user): RedirectResponse
    {
        $user->forceFill([
            'approved_at' => null,
            'approved_by_user_id' => null,
        ])->save();

        // Remove course access when revoking approval.
        $user->courses()->sync([]);

        return back()->with('status', __('Approval revoked.'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:'.implode(',', array_map(fn (UserRole $r) => $r->value, UserRole::cases()))],
        ]);

        $user->forceFill([
            'role' => $validated['role'],
        ])->save();

        return back()->with('status', __('Role updated.'));
    }

    public function updateCourses(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'course_ids' => ['array'],
            'course_ids.*' => ['integer', 'exists:courses,id'],
        ]);

        $courseIds = $validated['course_ids'] ?? [];
        $user->courses()->sync($courseIds);

        return back()->with('status', __('Course access updated.'));
    }

    public function updatePassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->forceFill([
            'password' => $validated['password'],
        ])->save();

        return back()->with('status', __('Password updated for :name.', ['name' => $user->name]));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return back()->with('error', __('You cannot delete your own account.'));
        }

        if ($user->isAdmin()) {
            $otherAdmins = User::query()
                ->where('role', UserRole::Admin)
                ->whereKeyNot($user->id)
                ->exists();
            if (! $otherAdmins) {
                return back()->with('error', __('Cannot delete the only administrator account.'));
            }
        }

        DB::transaction(function () use ($user) {
            User::query()->where('approved_by_user_id', $user->id)->update(['approved_by_user_id' => null]);
            $user->notifications()->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $user->delete();
        });

        return redirect()->route('admin.users.index')->with('status', __('User deleted.'));
    }
}
