<?php

namespace Tests\Feature\Phase2;

use App\Enums\UserRole;
use App\Models\ForumCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForumTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_user_can_create_thread(): void
    {
        $user = User::factory()->create(['role' => UserRole::Student, 'approved_at' => now()]);
        $cat = ForumCategory::query()->create(['name' => 'General', 'slug' => 'general', 'sort_order' => 1]);

        $this->actingAs($user)
            ->post(route('forums.threads.store', $cat), [
                'title' => 'Help me',
                'body' => 'Question here',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('forum_threads', ['title' => 'Help me', 'user_id' => $user->id]);
    }
}
