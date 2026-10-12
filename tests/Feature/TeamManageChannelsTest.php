<?php

namespace Tests\Feature;

use App\Enums\AssignmentStatus;
use App\Enums\SubmissionStatus;
use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Profile;
use App\Models\Team;
use App\Models\TeamChannel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamManageChannelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_assignment_list_has_no_tabs_and_shows_title_and_due(): void
    {
        [$instructor, $team] = $this->seedTeamWithStudent();
        $assignment = $this->createAssignment($team, $instructor, 'Lab Alpha', now()->addDay());

        $this->actingAs($instructor)
            ->get(route('teams.show', $team))
            ->assertOk()
            ->assertSee('Lab Alpha')
            ->assertDontSee(__('Upcoming'))
            ->assertDontSee(__('Past due'))
            ->assertDontSee(__('Completed'));
    }

    public function test_student_viewing_assignment_sets_viewed_status_for_staff_table(): void
    {
        [$instructor, $team, $student] = $this->seedTeamWithStudent();
        $assignment = $this->createAssignment($team, $instructor);

        $this->actingAs($student)
            ->get(route('teams.assignments.show', [$team, $assignment]))
            ->assertOk();

        $submission = AssignmentSubmission::query()
            ->where('assignment_id', $assignment->id)
            ->where('user_id', $student->id)
            ->firstOrFail();

        $this->assertNotNull($submission->viewed_at);
        $this->assertSame(SubmissionStatus::NotSubmitted, $submission->status);

        $this->actingAs($instructor)
            ->get(route('teams.assignments.show', [$team, $assignment]))
            ->assertOk()
            ->assertSee(__('Viewed'))
            ->assertSee($student->profile->display_name);

        $this->actingAs($instructor)
            ->get(route('teams.assignments.student', [$team, $assignment, $student]))
            ->assertOk()
            ->assertSee(__('Viewed'))
            ->assertSee(__('No files uploaded yet.'));
    }

    public function test_instructor_can_manage_custom_channels_students_cannot(): void
    {
        [$instructor, $team, $student] = $this->seedTeamWithStudent();

        $this->actingAs($student)
            ->get(route('teams.manage', $team))
            ->assertForbidden();

        $this->actingAs($instructor)
            ->get(route('teams.manage', ['team' => $team, 'tab' => 'channels']))
            ->assertOk()
            ->assertSee(__('Channels'))
            ->assertSee(__('Members'))
            ->assertDontSee(__('Analytics'))
            ->assertSee(__('Manage team'));

        $this->actingAs($instructor)
            ->post(route('teams.channels.store', $team), [
                'name' => 'Algorithms',
            ])
            ->assertRedirect(route('teams.manage', ['team' => $team, 'tab' => 'channels']));

        $channel = TeamChannel::query()
            ->where('team_id', $team->id)
            ->where('name', 'Algorithms')
            ->firstOrFail();

        $this->assertFalse($channel->is_general);

        $this->actingAs($student)
            ->get(route('teams.channels.show', [$team, $channel]))
            ->assertOk()
            ->assertSee('Algorithms')
            ->assertDontSee(__('Manage team'));

        $this->actingAs($instructor)
            ->put(route('teams.channels.update', [$team, $channel]), [
                'name' => 'Algorithms II',
            ])
            ->assertRedirect(route('teams.manage', ['team' => $team, 'tab' => 'channels']));

        $this->assertSame('Algorithms II', $channel->fresh()->name);

        $general = $team->generalChannel();
        $this->actingAs($instructor)
            ->delete(route('teams.channels.destroy', [$team, $general]))
            ->assertForbidden();

        $this->actingAs($instructor)
            ->delete(route('teams.channels.destroy', [$team, $channel]))
            ->assertRedirect(route('teams.manage', ['team' => $team, 'tab' => 'channels']));

        $this->assertDatabaseMissing('team_channels', ['id' => $channel->id]);
    }

    public function test_students_cannot_add_or_remove_members(): void
    {
        [$instructor, $team, $student] = $this->seedTeamWithStudent();
        $outsider = $this->makeUser(UserRole::Student, 'outsider@example.test', 'Outsider');

        $this->actingAs($student)
            ->post(route('teams.members.attach', $team), [
                'student_ids' => [$outsider->id],
            ])
            ->assertForbidden();

        $this->actingAs($student)
            ->delete(route('teams.members.detach', [$team, $instructor]))
            ->assertForbidden();
    }

    public function test_new_team_gets_general_channel(): void
    {
        $team = Team::query()->create(['name' => 'Fresh Team']);

        $this->assertDatabaseHas('team_channels', [
            'team_id' => $team->id,
            'slug' => 'general',
            'is_general' => true,
        ]);
    }

    /**
     * @return array{0: User, 1: Team, 2: User}
     */
    private function seedTeamWithStudent(): array
    {
        $instructor = $this->makeUser(UserRole::Instructor, 'teacher@example.test', 'Teacher');
        $student = $this->makeUser(UserRole::Student, 'student@example.test', 'Student');
        $team = Team::query()->create(['name' => 'Cohort Manage']);
        $team->users()->attach([$instructor->id, $student->id]);

        return [$instructor, $team->fresh(), $student];
    }

    private function createAssignment(Team $team, User $creator, string $title = 'Lab', $dueAt = null): Assignment
    {
        return Assignment::query()->create([
            'team_id' => $team->id,
            'created_by' => $creator->id,
            'title' => $title,
            'instructions' => 'Upload files',
            'due_at' => $dueAt,
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

        return $user->fresh('profile');
    }
}
