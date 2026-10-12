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
        $channel = $team->generalChannel();

        $this->actingAs($instructor)
            ->get(route('teams.channels.show', [$team, $channel]))
            ->assertOk()
            ->assertSee(__('Browse'))
            ->assertSee(__('Forum'))
            ->assertSee(__('Dashboard'))
            ->assertSee(__('Notifications'));
    }

    public function test_general_route_redirects_to_general_channel(): void
    {
        [$instructor, $team] = $this->seedTeamWithStudent();
        $channel = $team->generalChannel();

        $this->actingAs($instructor)
            ->get(route('teams.general', $team))
            ->assertRedirect(route('teams.channels.show', [$team, $channel]));
    }

    public function test_instructor_can_post_and_student_can_reply(): void
    {
        [$instructor, $team, $student] = $this->seedTeamWithStudent();
        $channel = $team->generalChannel();

        $this->actingAs($instructor)
            ->post(route('teams.channels.posts.store', [$team, $channel]), [
                'subject' => 'Welcome note',
                'banner_theme' => 'peach',
                'body' => '<p>Welcome to General.</p>',
            ])
            ->assertRedirect();

        $post = TeamPost::query()->where('team_id', $team->id)->whereNull('parent_id')->firstOrFail();
        $this->assertSame('Welcome note', $post->subject);
        $this->assertSame('#F5D5C8', $post->banner_bg);
        $this->assertTrue($post->body_is_html);
        $this->assertStringContainsString('Welcome to General.', $post->body);
        $this->assertSame($channel->id, $post->team_channel_id);

        $this->actingAs($student)
            ->post(route('teams.channels.posts.store', [$team, $channel]), [
                'body' => 'Student should not post.',
            ])
            ->assertForbidden();

        $this->actingAs($student)
            ->post(route('teams.channels.posts.replies.store', [$team, $channel, $post]), [
                'body' => 'Thanks!',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('team_posts', [
            'parent_id' => $post->id,
            'user_id' => $student->id,
            'body' => 'Thanks!',
            'team_channel_id' => $channel->id,
        ]);
    }

    public function test_at_all_notifies_other_team_members(): void
    {
        [$instructor, $team, $student] = $this->seedTeamWithStudent();
        $channel = $team->generalChannel();
        $other = $this->makeUser(UserRole::Student, 'other@example.test', 'Other');
        $team->users()->attach($other);

        $this->actingAs($instructor)
            ->post(route('teams.channels.posts.store', [$team, $channel]), [
                'body' => 'Hello @all please read this.',
            ])
            ->assertRedirect();

        $this->assertSame(0, $instructor->fresh()->notifications()->count());
        $this->assertSame(1, $student->fresh()->notifications()->count());
        $this->assertSame(1, $other->fresh()->notifications()->count());
    }

    public function test_named_mention_notifies_team_member_only(): void
    {
        [$instructor, $team, $student] = $this->seedTeamWithStudent();
        $channel = $team->generalChannel();
        $outsider = $this->makeUser(UserRole::Student, 'out@example.test', 'Outsider');

        $this->actingAs($instructor)
            ->post(route('teams.channels.posts.store', [$team, $channel]), [
                'body' => 'Hi @'.$student->profile->handle.' and @'.$outsider->profile->handle,
            ])
            ->assertRedirect();

        $this->assertSame(1, $student->fresh()->notifications()->count());
        $this->assertSame(0, $outsider->fresh()->notifications()->count());
    }

    public function test_non_member_cannot_view_or_reply(): void
    {
        [$instructor, $team] = $this->seedTeamWithStudent();
        $channel = $team->generalChannel();
        $stranger = $this->makeUser(UserRole::Student, 'stranger@example.test', 'Stranger');

        $post = TeamPost::query()->create([
            'team_id' => $team->id,
            'team_channel_id' => $channel->id,
            'user_id' => $instructor->id,
            'parent_id' => null,
            'body' => 'Staff only view test',
        ]);

        $this->actingAs($stranger)
            ->get(route('teams.channels.show', [$team, $channel]))
            ->assertForbidden();

        $this->actingAs($stranger)
            ->post(route('teams.channels.posts.replies.store', [$team, $channel, $post]), [
                'body' => 'Nope',
            ])
            ->assertForbidden();
    }

    public function test_members_can_toggle_reactions_on_posts_and_replies(): void
    {
        [$instructor, $team, $student] = $this->seedTeamWithStudent();
        $channel = $team->generalChannel();

        $post = TeamPost::query()->create([
            'team_id' => $team->id,
            'team_channel_id' => $channel->id,
            'user_id' => $instructor->id,
            'parent_id' => null,
            'subject' => 'Hello',
            'body' => 'React to this',
        ]);

        $this->actingAs($student)
            ->post(route('teams.channels.posts.reactions.toggle', [$team, $channel, $post]), [
                'emoji' => '👍',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('team_post_reactions', [
            'team_post_id' => $post->id,
            'user_id' => $student->id,
            'emoji' => '👍',
        ]);

        $this->actingAs($student)
            ->get(route('teams.channels.show', [$team, $channel]))
            ->assertOk()
            ->assertSee('👍')
            ->assertSee('Hello')
            ->assertDontSee(__('Start a post'))
            ->assertSee(__('Reply'));

        $this->actingAs($student)
            ->post(route('teams.channels.posts.reactions.toggle', [$team, $channel, $post]), [
                'emoji' => '👍',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('team_post_reactions', [
            'team_post_id' => $post->id,
            'user_id' => $student->id,
            'emoji' => '👍',
        ]);
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

        return [$instructor, $team->fresh(), $student];
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
