<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Profile;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTeamManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_team_and_attach_instructors_and_students(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $instructor = $this->createUser(UserRole::Instructor, 'instructor@example.test', 'Instructor');
        $student = $this->createUser(UserRole::Student, 'student@example.test', 'Student');
        $team = Team::query()->create(['name' => 'Cohort A']);

        $response = $this->actingAs($admin)->post(route('admin.teams.members.attach', $team), [
            'instructor_ids' => [$instructor->id],
            'student_ids' => [$student->id],
        ]);

        $response->assertRedirect(route('admin.teams.show', $team));
        $this->assertDatabaseHas('team_user', ['team_id' => $team->id, 'user_id' => $instructor->id]);
        $this->assertDatabaseHas('team_user', ['team_id' => $team->id, 'user_id' => $student->id]);
    }

    public function test_admin_can_detach_a_team_member(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $student = $this->createUser(UserRole::Student, 'student@example.test', 'Student');
        $team = Team::query()->create(['name' => 'Cohort A']);
        $team->users()->attach($student);

        $response = $this->actingAs($admin)->delete(route('admin.teams.members.detach', [$team, $student]));

        $response->assertRedirect(route('admin.teams.show', $team));
        $this->assertDatabaseMissing('team_user', ['team_id' => $team->id, 'user_id' => $student->id]);
    }

    public function test_creating_a_team_opens_the_team_detail_page(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->post(route('admin.teams.store'), [
            'name' => 'New Cohort',
        ]);

        $team = Team::query()->where('name', 'New Cohort')->firstOrFail();
        $response->assertRedirect(route('admin.teams.show', $team));
    }

    public function test_team_membership_rejects_users_in_the_wrong_role_field(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $student = $this->createUser(UserRole::Student, 'student@example.test', 'Student');
        $team = Team::query()->create(['name' => 'Cohort A']);

        $response = $this->actingAs($admin)->post(route('admin.teams.members.attach', $team), [
            'instructor_ids' => [$student->id],
            'student_ids' => [],
        ]);

        $response->assertSessionHasErrors('instructor_ids.0');
        $this->assertDatabaseMissing('team_user', ['team_id' => $team->id, 'user_id' => $student->id]);
    }

    public function test_users_tab_shows_each_users_team_membership(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $student = $this->createUser(UserRole::Student, 'student@example.test', 'Student');
        $team = Team::query()->create(['name' => 'Cohort A']);
        $team->users()->attach($student);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['tab' => 'users']))
            ->assertOk()
            ->assertSee('Student')
            ->assertSee('Cohort A')
            ->assertSee('Teams')
            ->assertSee('>1</', false);
    }

    public function test_only_admins_can_manage_teams(): void
    {
        $student = User::factory()->create(['role' => UserRole::Student]);

        $this->actingAs($student)
            ->post(route('admin.teams.store'), ['name' => 'Not allowed'])
            ->assertForbidden();

        $this->assertDatabaseMissing('teams', ['name' => 'Not allowed']);
    }

    public function test_teams_home_shows_card_grid_not_auto_opened_detail(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $team = Team::query()->create(['name' => 'Cohort A']);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['tab' => 'teams']))
            ->assertOk()
            ->assertSee('Your teams')
            ->assertSee('Cohort A')
            ->assertSee(route('admin.teams.show', $team, false), false)
            ->assertDontSee('Add members')
            ->assertDontSee('Add instructors')
            ->assertDontSee('Back to teams');
    }

    public function test_team_show_page_lists_members_and_management_actions(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $first = Team::query()->create(['name' => 'Alpha Team']);
        $second = Team::query()->create(['name' => 'Beta Team']);
        $student = $this->createUser(UserRole::Student, 'student@example.test', 'Student One');
        $second->users()->attach($student);

        $this->actingAs($admin)
            ->get(route('admin.teams.show', $second))
            ->assertOk()
            ->assertSee('Beta Team')
            ->assertSee('Student One')
            ->assertSee('Add instructors')
            ->assertSee('Add students')
            ->assertSee('Settings')
            ->assertSee('Back to teams')
            ->assertDontSee('Alpha Team');
    }

    private function createUser(UserRole $role, string $email, string $displayName): User
    {
        $user = User::factory()->create([
            'email' => $email,
            'role' => $role,
        ]);

        Profile::query()->create([
            'user_id' => $user->id,
            'handle' => strtolower(str_replace(' ', '', $displayName)),
            'display_name' => $displayName,
        ]);

        return $user;
    }
}
