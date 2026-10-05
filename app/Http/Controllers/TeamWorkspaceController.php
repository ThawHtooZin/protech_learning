<?php

namespace App\Http\Controllers;

use App\Enums\AssignmentStatus;
use App\Enums\SubmissionStatus;
use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Team;
use App\Models\User;
use App\Services\TeamAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeamWorkspaceController extends Controller
{
    public function __construct(
        private TeamAccessService $access,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $teams = $user->isAdmin()
            ? Team::query()->withCount('users')->orderBy('name')->get()
            : $user->teams()->withCount('users')->orderBy('name')->get();

        return view('teams.index', compact('teams'));
    }

    public function show(Request $request, Team $team): View
    {
        $user = $request->user();
        abort_unless($this->access->canViewTeam($user, $team), 403);

        $team->load(['users.profile'])
            ->loadCount('users');

        $canManageAssignments = $this->access->canManageAssignments($user, $team);
        $canManageStudents = $this->access->canManageStudents($user, $team);

        $assignments = $team->assignments()
            ->with(['submissions' => fn ($q) => $q->where('user_id', $user->id)])
            ->latest()
            ->get();

        $tab = $request->string('tab', 'upcoming')->toString();
        if (! in_array($tab, ['upcoming', 'past_due', 'completed'], true)) {
            $tab = 'upcoming';
        }

        $ownSubmissionMap = $assignments
            ->mapWithKeys(fn (Assignment $assignment) => [
                $assignment->id => $assignment->submissions->first(),
            ]);

        $filtered = $this->filterAssignmentsForTab($assignments, $tab, $user, $ownSubmissionMap);
        $grouped = $this->groupAssignmentsByDueDate($filtered);

        $counts = [
            'upcoming' => $this->filterAssignmentsForTab($assignments, 'upcoming', $user, $ownSubmissionMap)->count(),
            'past_due' => $this->filterAssignmentsForTab($assignments, 'past_due', $user, $ownSubmissionMap)->count(),
            'completed' => $this->filterAssignmentsForTab($assignments, 'completed', $user, $ownSubmissionMap)->count(),
        ];

        $teamNav = 'assignments';

        return view('teams.show', compact(
            'team',
            'canManageAssignments',
            'canManageStudents',
            'ownSubmissionMap',
            'tab',
            'grouped',
            'counts',
            'teamNav',
        ));
    }

    public function general(Request $request, Team $team): View
    {
        $user = $request->user();
        abort_unless($this->access->canViewTeam($user, $team), 403);

        $team->load(['users.profile'])->loadCount('users');

        $posts = $team->posts()
            ->whereNull('parent_id')
            ->with(['user.profile', 'replies.user.profile'])
            ->latest()
            ->get();

        $canManageStudents = $this->access->canManageStudents($user, $team);
        $canManageAssignments = $this->access->canManageAssignments($user, $team);
        $canPostOnGeneral = $this->access->canPostOnGeneral($user, $team);
        $canReplyOnGeneral = $this->access->canReplyOnGeneral($user, $team);

        $students = collect();
        if ($canManageStudents) {
            $students = User::query()
                ->with('profile')
                ->where('role', UserRole::Student)
                ->orderBy('email')
                ->get();
        }

        $teamNav = 'general';

        return view('teams.general', compact(
            'team',
            'canManageStudents',
            'canManageAssignments',
            'canPostOnGeneral',
            'canReplyOnGeneral',
            'students',
            'posts',
            'teamNav',
        ));
    }

    public function attachStudents(Request $request, Team $team): RedirectResponse
    {
        $user = $request->user();
        abort_unless($this->access->canManageStudents($user, $team), 403);

        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', Rule::exists('users', 'id')->where('role', UserRole::Student->value)],
        ]);

        $team->users()->syncWithoutDetaching($validated['student_ids']);

        return redirect()
            ->route('teams.general', $team)
            ->with('status', __('Students added.'));
    }

    public function detachMember(Request $request, Team $team, User $member): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($this->access->canManageStudents($actor, $team), 403);

        if (! $actor->isAdmin() && ! $member->isStudent()) {
            abort(403);
        }

        $team->users()->detach($member->id);

        return redirect()
            ->route('teams.general', $team)
            ->with('status', __('Member removed.'));
    }

    /**
     * @param  Collection<int, Assignment>  $assignments
     * @param  Collection<int, AssignmentSubmission|null>  $ownSubmissionMap
     * @return Collection<int, Assignment>
     */
    private function filterAssignmentsForTab(Collection $assignments, string $tab, User $user, Collection $ownSubmissionMap): Collection
    {
        $now = now();

        return $assignments->filter(function (Assignment $assignment) use ($tab, $user, $ownSubmissionMap, $now) {
            $own = $ownSubmissionMap->get($assignment->id);
            $turnedIn = $own && in_array($own->status, [SubmissionStatus::Submitted, SubmissionStatus::Returned], true);
            $isPastDue = $assignment->due_at !== null && $assignment->due_at->lt($now);
            $isClosed = $assignment->status === AssignmentStatus::Closed;

            if ($user->isStudent()) {
                return match ($tab) {
                    'completed' => $turnedIn || $isClosed,
                    'past_due' => ! $turnedIn && ! $isClosed && $isPastDue,
                    default => ! $turnedIn && ! $isClosed && ! $isPastDue,
                };
            }

            // Instructors / admins
            return match ($tab) {
                'completed' => $isClosed,
                'past_due' => ! $isClosed && $isPastDue,
                default => ! $isClosed && ! $isPastDue,
            };
        })->values();
    }

    /**
     * @param  Collection<int, Assignment>  $assignments
     * @return Collection<string, Collection<int, Assignment>>
     */
    private function groupAssignmentsByDueDate(Collection $assignments): Collection
    {
        return $assignments
            ->groupBy(function (Assignment $assignment) {
                if (! $assignment->due_at) {
                    return __('No due date');
                }

                return $assignment->due_at->timezone(config('app.timezone'))->format('M j, Y');
            });
    }
}
