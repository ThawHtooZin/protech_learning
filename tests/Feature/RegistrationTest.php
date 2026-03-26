<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_profile_and_is_pending_approval(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'handle' => 'testuser',
            'display_name' => 'Tester',
            'password' => 'password-12345',
            'password_confirmation' => 'password-12345',
        ]);

        $response->assertRedirect(route('approval.notice'));
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertDatabaseHas('profiles', ['handle' => 'testuser']);
        $this->assertAuthenticated();
    }
}
