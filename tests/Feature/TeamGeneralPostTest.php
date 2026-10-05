<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Profile;
use App\Models\Team;
use App\Models\TeamPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamGeneralPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_workspace_shows_full_learner_navbar(): void
    {
        [$instructor, $team] = $this->seedTeamWithStudent();

        $this->actingAs($instructor)
            ->get(route('teams.general', $team))
            ->assertOk()
            ->assertSee(__('Browse'))
            ->assertSee(__('Forum'))
            ->assertSee(__('Dashboard'))
            ->assertSee(__('Notifications'));
    }

    public function test_instructor_can_post_and_student_can_reply(): void
    {
        [$instructor, $team, $student] = $this->seedTeamWithStudent();

        $this->actingAs($instructor)
            ->post(route('teams.posts.store', $team), [
                'body' => 'Welcome to General.',
            ])
            ->assertRedirect(route('teams.general', $team));

        $post = TeamPost::query()->where('team_id', $team->id)->whereNull('parent_id')->firstOrFail();
        $this->assertSame('Welcome to General.', $post->body);

        $this->actingAs($student)
            ->post(route('teams.posts.store', $team), [
                'body' => 'Student should not post.',
            ])
            ->assertForbidden();

        $this->actingAs($student)
            ->post(route('teams.posts.replies.store', [$team, $post]), [
                'body' => 'Thanks!',
            ])
            ->assertRedirect(route('teams.general', $team));

        $this->assertDatabaseHas('team_posts', [
            'parent_id' => $post->id,
            'user_id' => $student->id,
            'body' => 'Thanks!',
        ]);
    }

    public function test_at_all_notifies_other_team_members(): void
    {
        [$instructor, $team, $student] = $this->seedTeamWithStudent();
        $other = $this->makeUser(UserRole::Student, 'other@example.test', 'Other');
        $team->users()->attach($other);

        $this->actingAs($instructor)
            ->post(route('teams.posts.store', $team), [
                'body' => 'Hello @all please read this.',
            ])
            ->assertRedirect(route('teams.general', $team));

        $this->assertSame(0, $instructor->fresh()->notifications()->count());
        $this->assertSame(1, $student->fresh()->notifications()->count());
        $this->assertSame(1, $other->fresh()->notifications()->count());
    }

    public function test_named_mention_notifies_team_member_only(): void
    {
        [$instructor, $team, $student] = $this->seedTeamWithStudent();
        $outsider = $this->makeUser(UserRole::Student, 'out@example.test', 'Outsider');

        $this->actingAs($instructor)
            ->post(route('teams.posts.store', $team), [
                'body' => 'Hi @'.$student->profile->handle.' and @'.$outsider->profile->handle,
            ])
            ->assertRedirect(route('teams.general', $team));

        $this->assertSame(1, $student->fresh()->notifications()->count());
        $this->assertSame(0, $outsider->fresh()->notifications()->count());
    }

    public function test_non_member_cannot_view_or_reply(): void
    {
        [$instructor, $team] = $this->seedTeamWithStudent();
        $stranger = $this->makeUser(UserRole::Student, 'stranger@example.test', 'Stranger');

        $post = TeamPost::query()->create([
            'team_id' => $team->id,
            'user_id' => $instructor->id,
            'parent_id' => null,
            'body' => 'Staff only view test',
        ]);

        $this->actingAs($stranger)
            ->get(route('teams.general', $team))
            ->assertForbidden();

        $this->actingAs($stranger)
            ->post(route('teams.posts.replies.store', [$team, $post]), [
                'body' => 'Nope',
            ])
            ->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Team, 2: User}
     */
    private function seedTeamWithStudent(): array
    {
        $instructor = $this->makeUser(UserRole::Instructor, 'teacher@example.test', 'Teacher');
        $student = $this->makeUser(UserRole::Student, 'student@example.test', 'Student');
        $team = Team::query()->create(['name' => 'Cohort Posts']);
        $team->users()->attach([$instructor->id, $student->id]);

        return [$instructor, $team, $student];
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
