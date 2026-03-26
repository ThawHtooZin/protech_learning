<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_another_user(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@test.local',
            'password' => Hash::make('secret'),
            'role' => UserRole::Admin,
        ]);

        $target = User::query()->create([
            'name' => 'Student',
            'email' => 'student@test.local',
            'password' => Hash::make('secret'),
            'role' => UserRole::Student,
        ]);
        Profile::query()->create([
            'user_id' => $target->id,
            'handle' => 'student1',
            'display_name' => 'Student',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $target));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
        $this->assertDatabaseMissing('profiles', ['user_id' => $target->id]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@test.local',
            'password' => Hash::make('secret'),
            'role' => UserRole::Admin,
        ]);

        $response = $this->actingAs($admin)->from(route('admin.users.show', $admin))->delete(route('admin.users.destroy', $admin));

        $response->assertRedirect(route('admin.users.show', $admin));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_delete_peer_when_another_admin_exists(): void
    {
        $adminA = User::query()->create([
            'name' => 'Admin A',
            'email' => 'admina@test.local',
            'password' => Hash::make('secret'),
            'role' => UserRole::Admin,
        ]);
        $adminB = User::query()->create([
            'name' => 'Admin B',
            'email' => 'adminb@test.local',
            'password' => Hash::make('secret'),
            'role' => UserRole::Admin,
        ]);

        $response = $this->actingAs($adminA)->delete(route('admin.users.destroy', $adminB));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $adminB->id]);
        $this->assertDatabaseHas('users', ['id' => $adminA->id]);
    }
}
