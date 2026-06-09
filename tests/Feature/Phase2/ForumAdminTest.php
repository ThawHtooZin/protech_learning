<?php

namespace Tests\Feature\Phase2;

use App\Enums\UserRole;
use App\Models\ForumCategory;
use App\Models\ForumThread;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForumAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_and_delete_tag(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'approved_at' => now()]);
        $tag = Tag::query()->create(['name' => 'Old', 'slug' => 'old']);

        $this->actingAs($admin)
            ->put(route('admin.forums.tags.update', $tag), ['name' => 'New'])
            ->assertRedirect(route('admin.forums.tags'));

        $this->assertEquals('New', $tag->fresh()->name);

        $this->actingAs($admin)
            ->delete(route('admin.forums.tags.destroy', $tag))
            ->assertRedirect(route('admin.forums.tags'));

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_cannot_delete_category_with_threads(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'approved_at' => now()]);
        $cat = ForumCategory::query()->create(['name' => 'General', 'slug' => 'general', 'sort_order' => 1]);
        ForumThread::query()->create([
            'forum_category_id' => $cat->id,
            'user_id' => $admin->id,
            'title' => 'Hi',
            'slug' => 'hi',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.forums.categories.destroy', $cat))
            ->assertRedirect(route('admin.forums.categories'))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('forum_categories', ['id' => $cat->id]);
    }
}
