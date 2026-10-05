<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccountCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_approved_account_with_selected_role_and_profile(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->post(route('admin.users.store', ['tab' => 'users']), [
            'display_name' => 'New Instructor',
            'username' => 'new_instructor',
            'email' => 'instructor@example.test',
            'role' => UserRole::Instructor->value,
            'password' => 'strong-password-123',
            'password_confirmation' => 'strong-password-123',
        ]);

        $response->assertRedirect(route('admin.users.index', ['tab' => 'users']));
        $this->assertDatabaseHas('users', [
            'email' => 'instructor@example.test',
            'role' => UserRole::Instructor->value,
            'approved_by_user_id' => $admin->id,
        ]);
        $this->assertDatabaseHas('profiles', [
            'handle' => 'new_instructor',
            'display_name' => 'New Instructor',
        ]);
    }

    public function test_non_admin_cannot_create_accounts(): void
    {
        $student = User::factory()->create(['role' => UserRole::Student]);

        $this->actingAs($student)
            ->post(route('admin.users.store'), [
                'display_name' => 'Someone',
                'username' => 'someone',
                'email' => 'someone@example.test',
                'role' => UserRole::Admin->value,
                'password' => 'strong-password-123',
                'password_confirmation' => 'strong-password-123',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'someone@example.test']);
    }

    public function test_users_tab_opens_account_creation_in_a_dialog(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['tab' => 'users']))
            ->assertOk()
            ->assertSee('id="open-account-dialog"', false)
            ->assertSee('<dialog id="create-account-dialog"', false)
            ->assertSee('name="role"', false);
    }
}
