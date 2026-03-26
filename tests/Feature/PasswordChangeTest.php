<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_user_can_change_password_with_current_password(): void
    {
        $user = User::query()->create([
            'name' => 'Learner',
            'email' => 'learner@test.local',
            'password' => Hash::make('Current-pass1'),
            'role' => UserRole::Student,
        ]);
        $user->forceFill(['approved_at' => now()])->save();
        Profile::query()->create([
            'user_id' => $user->id,
            'handle' => 'learner1',
            'display_name' => 'Learner',
        ]);

        $response = $this->actingAs($user)->put(route('profiles.password'), [
            'current_password' => 'Current-pass1',
            'password' => 'New-pass-12345',
            'password_confirmation' => 'New-pass-12345',
        ]);

        $response->assertRedirect(route('profiles.edit'));
        $response->assertSessionHas('status');
        $user->refresh();
        $this->assertTrue(Hash::check('New-pass-12345', $user->password));
    }

    public function test_user_cannot_change_password_with_wrong_current(): void
    {
        $user = User::query()->create([
            'name' => 'Learner',
            'email' => 'learner2@test.local',
            'password' => Hash::make('Current-pass1'),
            'role' => UserRole::Student,
        ]);
        $user->forceFill(['approved_at' => now()])->save();
        Profile::query()->create([
            'user_id' => $user->id,
            'handle' => 'learner2',
            'display_name' => 'Learner',
        ]);

        $response = $this->actingAs($user)->from(route('profiles.edit'))->put(route('profiles.password'), [
            'current_password' => 'wrong-password',
            'password' => 'New-pass-12345',
            'password_confirmation' => 'New-pass-12345',
        ]);

        $response->assertRedirect(route('profiles.edit'));
        $response->assertSessionHasErrors('current_password');
        $user->refresh();
        $this->assertTrue(Hash::check('Current-pass1', $user->password));
    }

    public function test_admin_can_set_user_password(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@test.local',
            'password' => Hash::make('admin-pass'),
            'role' => UserRole::Admin,
        ]);

        $target = User::query()->create([
            'name' => 'Student',
            'email' => 'student@test.local',
            'password' => Hash::make('old-pass'),
            'role' => UserRole::Student,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.users.password', $target), [
            'password' => 'Reset-pass-12345',
            'password_confirmation' => 'Reset-pass-12345',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $target->refresh();
        $this->assertTrue(Hash::check('Reset-pass-12345', $target->password));
    }
}
