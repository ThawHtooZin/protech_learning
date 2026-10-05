<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_admin_with_profile(): void
    {
        $this->seed();

        $this->assertDatabaseHas('users', [
            'email' => 'admin@gmail.com',
            'role' => UserRole::Admin->value,
        ]);
        $this->assertDatabaseHas('profiles', [
            'handle' => 'admin',
            'display_name' => 'Admin',
        ]);
    }
}
