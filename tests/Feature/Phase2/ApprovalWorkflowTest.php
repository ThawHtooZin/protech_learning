<?php

namespace Tests\Feature\Phase2;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_unapproved_student_cannot_access_dashboard(): void
    {
        $user = User::factory()->create(['role' => UserRole::Student, 'approved_at' => null]);

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('approval.notice'));
    }

    public function test_admin_can_approve_user(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'approved_at' => now()]);
        $student = User::factory()->create(['role' => UserRole::Student, 'approved_at' => null]);

        $this->actingAs($admin)
            ->post(route('admin.users.approve', $student))
            ->assertRedirect();

        $this->assertNotNull($student->fresh()->approved_at);
    }
}
