<?php

namespace App\Http\Controllers;

use App\Enums\AssignmentStatus;
use App\Enums\SubmissionStatus;
use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\AssignmentAttachment;
use App\Models\AssignmentSubmission;
use App\Models\AssignmentSubmissionFile;
use App\Models\Team;
use App\Services\ActivityLogger;
use App\Services\AssignmentFileService;
use App\Services\TeamAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssignmentController extends Controller
{
    public function __construct(
        private TeamAccessService $access,
        private AssignmentFileService $files,
        private ActivityLogger $logger,
    ) {}

    public function create(Request $request, Team $team): View
    {
        abort_unless($this->access->canManageAssignments($request->user(), $team), 403);

        return view('teams.assignments.create', [
            'team' => $team,
            'teamNav' => 'assignments',
        ]);
    }

    public function store(Request $request, Team $team): RedirectResponse
    {
        $user = $request->user();
        abort_unless($this->access->canManageAssignments($user, $team), 403);

        $validated = $this->validateAssignment($request);

        $assignment = DB::transaction(function () use ($validated, $team, $user, $request) {
            $assignment = $team->assignments()->create([
                'created_by' => $user->id,
                'title' => $validated['title'],
                'instructions' => $validated['instructions'] ?? null,
                'due_at' => $validated['due_at'] ?? null,
                'status' => AssignmentStatus::Open,
            ]);

            if ($request->hasFile('resources')) {
                $this->files->storeAttachments($assignment, $request->file('resources'));
            }

            return $assignment;
        });

        $this->logger->assignmentInstant($user, 'assignment_created', $team, $assignment, [
            'title' => $assignment->title,
        ]);

        return redirect()
            ->route('teams.assignments.show', [$team, $assignment])
            ->with('status', __('Assignment created.'));
    }

    public function show(Request $request, Team $team, Assignment $assignment): View
    {
        $user = $request->user();
        $this->assertAssignmentTeam($team, $assignment);
        abort_unless($this->access->canViewTeam($user, $team), 403);

        $assignment->load(['attachments', 'creator.profile']);

        $canManage = $this->access->canManageAssignments($user, $team);
        $canSubmit = $this->access->canSubmit($user, $assignment);

        $roster = collect();
        $ownSubmission = null;

        if ($canManage) {
            $roster = $team->users()
                ->with('profile')
                ->where('users.role', UserRole::Student)
                ->orderBy('email')
                ->get();

            $assignment->load(['submissions.files', 'submissions.user.profile']);
        } elseif ($canSubmit || $user->isStudent()) {
            abort_unless($this->access->isMember($user, $team), 403);
            $ownSubmission = $assignment->submissions()
                ->with('files')
                ->where('user_id', $user->id)
                ->first();
        }

        return view('teams.assignments.show', [
            'team' => $team,
            'assignment' => $assignment,
            'canManage' => $canManage,
            'canSubmit' => $canSubmit,
            'roster' => $roster,
            'ownSubmission' => $ownSubmission,
            'teamNav' => 'assignments',
        ]);
    }

    public function edit(Request $request, Team $team, Assignment $assignment): View
    {
        $this->assertAssignmentTeam($team, $assignment);
        abort_unless($this->access->canManageAssignments($request->user(), $team), 403);

        $assignment->load('attachments');

        return view('teams.assignments.edit', [
            'team' => $team,
            'assignment' => $assignment,
            'teamNav' => 'assignments',
        ]);
    }

    public function update(Request $request, Team $team, Assignment $assignment): RedirectResponse
    {
        $this->assertAssignmentTeam($team, $assignment);
        abort_unless($this->access->canManageAssignments($request->user(), $team), 403);

        $validated = $this->validateAssignment($request);

        $assignment->update([
            'title' => $validated['title'],
            'instructions' => $validated['instructions'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
        ]);

        if ($request->hasFile('resources')) {
            $this->files->storeAttachments($assignment, $request->file('resources'));
        }

        return redirect()
            ->route('teams.assignments.show', [$team, $assignment])
            ->with('status', __('Assignment updated.'));
    }

    public function close(Request $request, Team $team, Assignment $assignment): RedirectResponse
    {
        $this->assertAssignmentTeam($team, $assignment);
        abort_unless($this->access->canManageAssignments($request->user(), $team), 403);

        $assignment->update(['status' => AssignmentStatus::Closed]);

        return redirect()
            ->route('teams.assignments.show', [$team, $assignment])
            ->with('status', __('Assignment closed. Students can no longer upload.'));
    }

    public function destroy(Request $request, Team $team, Assignment $assignment): RedirectResponse
    {
        $user = $request->user();
        $this->assertAssignmentTeam($team, $assignment);
        abort_unless($this->access->canManageAssignments($user, $team), 403);

        $this->files->deleteAssignmentFiles($assignment);
        $assignment->delete();

        return redirect()
            ->route('teams.show', $team)
            ->with('status', __('Assignment deleted.'));
    }

    public function submit(Request $request, Team $team, Assignment $assignment): RedirectResponse
    {
        $user = $request->user();
        $this->assertAssignmentTeam($team, $assignment);
        abort_unless($this->access->canSubmit($user, $assignment), 403);

        if (! $assignment->isOpen()) {
            return back()->withErrors(['files' => __('This assignment is closed.')]);
        }

        $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:'.$this->files->maxFiles()],
            'files.*' => $this->files->fileRules(),
        ]);

        $submission = AssignmentSubmission::query()->firstOrCreate(
            [
                'assignment_id' => $assignment->id,
                'user_id' => $user->id,
            ],
            [
                'status' => SubmissionStatus::NotSubmitted,
            ],
        );

        $submission->load('files');
        $this->files->replaceSubmissionFiles($submission, $request->file('files'));

        $submission->update([
            'status' => SubmissionStatus::Submitted,
            'submitted_at' => now(),
            'returned_at' => null,
            'feedback' => null,
        ]);

        $this->logger->assignmentInstant($user, 'assignment_submitted', $team, $assignment);

        return redirect()
            ->route('teams.assignments.show', [$team, $assignment])
            ->with('assignment_turned_in', true)
            ->with('status', __('Turned in.'));
    }

    public function returnSubmission(
        Request $request,
        Team $team,
        Assignment $assignment,
        AssignmentSubmission $submission,
    ): RedirectResponse {
        $user = $request->user();
        $this->assertAssignmentTeam($team, $assignment);
        abort_unless($this->access->canReview($user, $assignment), 403);
        abort_unless($submission->assignment_id === $assignment->id, 404);

        $validated = $request->validate([
            'feedback' => ['nullable', 'string', 'max:5000'],
        ]);

        $submission->update([
            'status' => SubmissionStatus::Returned,
            'feedback' => $validated['feedback'] ?? null,
            'returned_at' => now(),
        ]);

        $this->logger->assignmentInstant($user, 'assignment_returned', $team, $assignment, [
            'student_id' => $submission->user_id,
        ]);

        return redirect()
            ->route('teams.assignments.show', [$team, $assignment])
            ->with('status', __('Submission returned to student.'));
    }

    public function downloadAttachment(
        Request $request,
        Team $team,
        Assignment $assignment,
        AssignmentAttachment $attachment,
    ): StreamedResponse {
        $this->assertAssignmentTeam($team, $assignment);
        abort_unless($this->access->canViewTeam($request->user(), $team), 403);
        abort_unless($attachment->assignment_id === $assignment->id, 404);

        return Storage::disk($attachment->disk)->download(
            $attachment->path,
            $attachment->original_name,
        );
    }

    public function downloadSubmissionFile(
        Request $request,
        Team $team,
        Assignment $assignment,
        AssignmentSubmission $submission,
        AssignmentSubmissionFile $file,
    ): StreamedResponse {
        $user = $request->user();
        $this->assertAssignmentTeam($team, $assignment);
        abort_unless($submission->assignment_id === $assignment->id, 404);
        abort_unless($file->assignment_submission_id === $submission->id, 404);

        $canReview = $this->access->canReview($user, $assignment);
        $isOwner = $submission->user_id === $user->id;
        abort_unless($canReview || $isOwner, 403);
        abort_unless($canReview || $this->access->isMember($user, $team), 403);

        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }

    /**
     * @return array{title: string, instructions?: string|null, due_at?: string|null}
     */
    private function validateAssignment(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'instructions' => ['nullable', 'string', 'max:10000'],
            'due_at' => ['nullable', 'date'],
            'resources' => ['sometimes', 'array', 'max:'.$this->files->maxFiles()],
            'resources.*' => $this->files->fileRules(),
        ]);
    }

    private function assertAssignmentTeam(Team $team, Assignment $assignment): void
    {
        abort_unless($assignment->team_id === $team->id, 404);
    }
}
