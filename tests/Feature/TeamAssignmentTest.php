<?php

namespace Tests\Feature;

use App\Enums\AssignmentStatus;
use App\Enums\SubmissionStatus;
use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Profile;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeamAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_on_team_can_create_assignment_and_team_b_students_cannot_see_it(): void
    {
        [$instructor, $teamA, $studentA, $studentB] = $this->seedTwoTeams();

        $response = $this->actingAs($instructor)->post(route('teams.assignments.store', $teamA), [
            'title' => 'HTML Lab 1',
            'instructions' => 'Upload your zip.',
        ]);

        $assignment = Assignment::query()->where('title', 'HTML Lab 1')->firstOrFail();
        $response->assertRedirect(route('teams.assignments.show', [$teamA, $assignment]));

        $this->actingAs($studentA)
            ->get(route('teams.assignments.show', [$teamA, $assignment]))
            ->assertOk()
            ->assertSee('HTML Lab 1');

        $this->actingAs($studentB)
            ->get(route('teams.assignments.show', [$teamA, $assignment]))
            ->assertForbidden();
    }

    public function test_student_can_submit_and_instructor_can_return_with_feedback(): void
    {
        Storage::fake('local');

        [$instructor, $team, $student] = $this->seedTeamWithStudent();
        $assignment = $this->createAssignment($team, $instructor);

        $file = UploadedFile::fake()->create('work.zip', 100, 'application/zip');

        $this->actingAs($student)
            ->post(route('teams.assignments.submit', [$team, $assignment]), [
                'files' => [$file],
            ])
            ->assertRedirect(route('teams.assignments.show', [$team, $assignment]));

        $submission = AssignmentSubmission::query()
            ->where('assignment_id', $assignment->id)
            ->where('user_id', $student->id)
            ->firstOrFail();

        $this->assertSame(SubmissionStatus::Submitted, $submission->status);
        Storage::disk('local')->assertExists($submission->files()->first()->path);

        $this->actingAs($instructor)
            ->post(route('teams.assignments.return', [$team, $assignment, $submission]), [
                'feedback' => 'Nice work — fix the README.',
            ])
            ->assertRedirect(route('teams.assignments.show', [$team, $assignment]));

        $this->assertSame(SubmissionStatus::Returned, $submission->fresh()->status);
        $this->assertSame('Nice work — fix the README.', $submission->fresh()->feedback);

        $this->actingAs($student)
            ->get(route('teams.assignments.show', [$team, $assignment]))
            ->assertOk()
            ->assertSee('Nice work — fix the README.');
    }

    public function test_closed_assignment_rejects_new_uploads(): void
    {
        Storage::fake('local');

        [$instructor, $team, $student] = $this->seedTeamWithStudent();
        $assignment = $this->createAssignment($team, $instructor);
        $assignment->update(['status' => AssignmentStatus::Closed]);

        $this->actingAs($student)
            ->from(route('teams.assignments.show', [$team, $assignment]))
            ->post(route('teams.assignments.submit', [$team, $assignment]), [
                'files' => [UploadedFile::fake()->create('work.pdf', 50, 'application/pdf')],
            ])
            ->assertRedirect(route('teams.assignments.show', [$team, $assignment]))
            ->assertSessionHasErrors('files');
    }

    public function test_instructor_cannot_use_admin_user_or_course_routes_but_can_manage_team_students(): void
    {
        [$instructor, $team, $student] = $this->seedTeamWithStudent();
        $outsider = $this->makeUser(UserRole::Student, 'outsider@example.test', 'Outsider');

        $this->actingAs($instructor)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($instructor)
            ->get(route('admin.courses.index'))
            ->assertForbidden();

        $this->actingAs($instructor)
            ->post(route('teams.members.attach', $team), [
                'student_ids' => [$outsider->id],
            ])
            ->assertRedirect(route('teams.general', $team));

        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $outsider->id,
        ]);

        $this->actingAs($instructor)
            ->delete(route('teams.members.detach', [$team, $student]))
            ->assertRedirect(route('teams.general', $team));

        $this->assertDatabaseMissing('team_user', [
            'team_id' => $team->id,
            'user_id' => $student->id,
        ]);
    }

    public function test_admin_can_manage_assignments_on_any_team(): void
    {
        $admin = $this->makeUser(UserRole::Admin, 'admin@example.test', 'Admin');
        $team = Team::query()->create(['name' => 'Cohort A']);

        $this->actingAs($admin)
            ->post(route('teams.assignments.store', $team), [
                'title' => 'Admin Task',
                'instructions' => 'Do the thing.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('assignments', [
            'team_id' => $team->id,
            'title' => 'Admin Task',
            'created_by' => $admin->id,
        ]);
    }

    public function test_instructor_not_on_team_cannot_create_assignment(): void
    {
        $instructor = $this->makeUser(UserRole::Instructor, 'teacher@example.test', 'Teacher');
        $team = Team::query()->create(['name' => 'Cohort A']);

        $this->actingAs($instructor)
            ->post(route('teams.assignments.store', $team), [
                'title' => 'Should fail',
            ])
            ->assertForbidden();
    }

    public function test_student_can_submit_any_file_type_including_code(): void
    {
        Storage::fake('local');

        [$instructor, $team, $student] = $this->seedTeamWithStudent();
        $assignment = $this->createAssignment($team, $instructor);

        $this->actingAs($student)
            ->post(route('teams.assignments.submit', [$team, $assignment]), [
                'files' => [
                    UploadedFile::fake()->create('app.py', 10, 'text/x-python'),
                    UploadedFile::fake()->create('index.html', 8, 'text/html'),
                ],
            ])
            ->assertRedirect(route('teams.assignments.show', [$team, $assignment]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'status' => SubmissionStatus::Submitted->value,
        ]);
    }

    public function test_student_cannot_download_another_students_files(): void
    {
        Storage::fake('local');

        [$instructor, $team, $studentA] = $this->seedTeamWithStudent();
        $studentB = $this->makeUser(UserRole::Student, 'student-b@example.test', 'Student B');
        $team->users()->attach($studentB);

        $assignment = $this->createAssignment($team, $instructor);

        $this->actingAs($studentA)
            ->post(route('teams.assignments.submit', [$team, $assignment]), [
                'files' => [UploadedFile::fake()->create('a.pdf', 20, 'application/pdf')],
            ]);

        $submission = AssignmentSubmission::query()
            ->where('assignment_id', $assignment->id)
            ->where('user_id', $studentA->id)
            ->firstOrFail();
        $file = $submission->files()->firstOrFail();

        $this->actingAs($studentB)
            ->get(route('teams.assignments.files.download', [$team, $assignment, $submission, $file]))
            ->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Team, 2: User, 3: User}
     */
    private function seedTwoTeams(): array
    {
        $instructor = $this->makeUser(UserRole::Instructor, 'teacher@example.test', 'Teacher');
        $teamA = Team::query()->create(['name' => 'Team A']);
        $teamB = Team::query()->create(['name' => 'Team B']);
        $studentA = $this->makeUser(UserRole::Student, 'a@example.test', 'Student A');
        $studentB = $this->makeUser(UserRole::Student, 'b@example.test', 'Student B');

        $teamA->users()->attach([$instructor->id, $studentA->id]);
        $teamB->users()->attach($studentB->id);

        return [$instructor, $teamA, $studentA, $studentB];
    }

    /**
     * @return array{0: User, 1: Team, 2: User}
     */
    private function seedTeamWithStudent(): array
    {
        $instructor = $this->makeUser(UserRole::Instructor, 'teacher@example.test', 'Teacher');
        $student = $this->makeUser(UserRole::Student, 'student@example.test', 'Student');
        $team = Team::query()->create(['name' => 'Cohort A']);
        $team->users()->attach([$instructor->id, $student->id]);

        return [$instructor, $team, $student];
    }

    private function createAssignment(Team $team, User $creator): Assignment
    {
        return Assignment::query()->create([
            'team_id' => $team->id,
            'created_by' => $creator->id,
            'title' => 'Lab',
            'instructions' => 'Upload files',
            'status' => AssignmentStatus::Open,
        ]);
    }

    private function makeUser(UserRole $role, string $email, string $displayName): User
    {
        $user = User::factory()->create([
            'email' => $email,
            'role' => $role,
        ]);
        $user->forceFill(['approved_at' => now()])->save();

        Profile::query()->create([
            'user_id' => $user->id,
            'handle' => strtolower(str_replace([' ', '@', '.'], '', $displayName.$user->id)),
            'display_name' => $displayName,
        ]);

        return $user;
    }
}
