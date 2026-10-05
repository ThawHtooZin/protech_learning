<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Profile;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_counts_and_pending_users(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'approved_at' => now(),
        ]);
        Profile::query()->create([
            'user_id' => $admin->id,
            'handle' => 'adminuser',
            'display_name' => 'Admin User',
        ]);

        Course::query()->create([
            'title' => 'Intro HTML',
            'slug' => 'intro-html',
            'is_published' => true,
        ]);
        Team::query()->create(['name' => 'Cohort A']);

        $pending = User::factory()->create([
            'email' => 'pending@example.test',
            'role' => UserRole::Student,
            'approved_at' => null,
        ]);
        Profile::query()->create([
            'user_id' => $pending->id,
            'handle' => 'pendingstudent',
            'display_name' => 'Pending Student',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Overview')
            ->assertSee('Needs attention')
            ->assertSee('Pending Student')
            ->assertSee('pending@example.test')
            ->assertSee('Intro HTML')
            ->assertSee(__('Awaiting approval'))
            ->assertSee(__('Recent courses'))
            ->assertSee(__('Recent activity'));
    }

    public function test_non_admin_cannot_open_admin_dashboard(): void
    {
        $student = User::factory()->create([
            'role' => UserRole::Student,
            'approved_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_sidebar_labels_teams_not_users(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'approved_at' => now(),
        ]);

        $html = $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('>Teams</', $html);
        $this->assertStringNotContainsString('Question bank', $html);
        $this->assertStringNotContainsString('+ New course', $html);
        // Sidebar destinations only — create actions live on page CTAs / Courses page.
        $this->assertDoesNotMatchRegularExpression(
            '/md:w-64[\s\S]*?>Users<\/a>/',
            $html
        );
    }
}
