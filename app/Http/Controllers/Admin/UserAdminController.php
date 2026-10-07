<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserAdminController extends Controller
{
    public function index(Request $request): View
    {
        $activeTab = $request->query('tab') === 'users' ? 'users' : 'teams';
        $teamCount = Team::query()->count();
        $userCount = User::query()->count();
        $teams = collect();
        $users = null;

        if ($activeTab === 'teams') {
            $teams = Team::query()
                ->withCount('users')
                ->orderBy('name')
                ->get();
        }

        if ($activeTab === 'users') {
            $users = User::query()
                ->with(['profile', 'teams'])
                ->orderByDesc('created_at')
                ->paginate(30)
                ->withQueryString();
        }

        return view('admin.users.index', compact(
            'activeTab',
            'teams',
            'teamCount',
            'userCount',
            'users',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'regex:/^[a-zA-Z0-9_]{2,32}$/', 'unique:profiles,handle'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', 'in:'.implode(',', array_map(fn (UserRole $role) => $role->value, UserRole::cases()))],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $user = new User([
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => UserRole::from($validated['role']),
            ]);
            $user->forceFill([
                'approved_at' => now(),
                'approved_by_user_id' => $request->user()->id,
            ])->save();

            $user->profile()->create([
                'handle' => $validated['username'],
                'display_name' => $validated['display_name'],
            ]);
        });

        return redirect()->route('admin.users.index', ['tab' => 'users'])->with('status', __('Account created.'));
    }

    public function show(User $user): View
    {
        $user->load('profile', 'teams');

        return view('admin.users.show', compact('user'));
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
