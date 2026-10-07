<?php

namespace Tests\Feature;

use App\Enums\CourseAccessType;
use App\Enums\PurchaseStatus;
use App\Enums\UserRole;
use App\Models\BankAccount;
use App\Models\Course;
use App\Models\CoursePurchase;
use App\Models\Enrollment;
use App\Models\Team;
use App\Models\User;
use App\Notifications\CourseAccessGrantedNotification;
use App\Notifications\CourseAccessRevokedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseCommerceUxTest extends TestCase
{
    use RefreshDatabase;

    private function approvedStudent(string $email = 'buyer@test.local'): User
    {
        $user = User::query()->create([
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => UserRole::Student,
        ]);
        $user->forceFill(['approved_at' => now()])->save();

        return $user;
    }

    private function admin(): User
    {
        $user = User::query()->create([
            'email' => 'admin-ux@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
        ]);
        $user->forceFill(['approved_at' => now()])->save();

        return $user;
    }

    private function publicCourse(): Course
    {
        return Course::query()->create([
            'title' => 'Paid Course',
            'slug' => 'paid-course',
            'description' => '<p><strong>Bold</strong> intro</p>',
            'is_published' => true,
            'access_type' => CourseAccessType::Public,
            'price_mmk' => 15000,
            'is_listed' => true,
        ]);
    }

    public function test_buy_modal_lists_multiple_bank_accounts(): void
    {
        $course = $this->publicCourse();
        BankAccount::query()->create([
            'name' => 'KBZPay',
            'account_name' => 'A',
            'account_number' => '111',
            'sort_order' => 0,
            'is_active' => true,
        ]);
        BankAccount::query()->create([
            'name' => 'Wave',
            'account_name' => 'B',
            'account_number' => '222',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        BankAccount::query()->create([
            'name' => 'Hidden',
            'account_name' => 'C',
            'account_number' => '333',
            'is_active' => false,
        ]);

        $student = $this->approvedStudent();

        $this->actingAs($student)
            ->get(route('courses.show', $course))
            ->assertOk()
            ->assertSee('KBZPay')
            ->assertSee('Wave')
            ->assertDontSee('Hidden')
            ->assertSee('Bold', false)
            ->assertSee('buyOpen', false);
    }

    public function test_admin_can_upload_course_cover(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $course = $this->publicCourse();

        $this->actingAs($admin)
            ->put(route('admin.courses.update', $course), [
                'title' => $course->title,
                'description' => '<p>Updated</p>',
                'access_type' => 'public',
                'price_mmk' => 15000,
                'is_listed' => '1',
                'is_published' => '1',
                'cover' => UploadedFile::fake()->image('cover.jpg'),
            ])
            ->assertRedirect(route('admin.courses.edit', $course));

        $course->refresh();
        $this->assertNotNull($course->cover_path);
        Storage::disk('public')->assertExists($course->cover_path);
    }

    public function test_purchase_approve_notifies_student(): void
    {
        Notification::fake();
        Storage::fake('local');
        $course = $this->publicCourse();
        $student = $this->approvedStudent();
        $admin = $this->admin();

        $purchase = CoursePurchase::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'slip_path' => 'x.jpg',
            'slip_disk' => 'local',
            'status' => PurchaseStatus::Pending,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.purchases.approve', $purchase))
            ->assertRedirect();

        Notification::assertSentTo($student, CourseAccessGrantedNotification::class);
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_private_grant_and_team_grant_notify(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $student = $this->approvedStudent();
        $course = Course::query()->create([
            'title' => 'Private',
            'slug' => 'private-ux',
            'is_published' => true,
            'access_type' => CourseAccessType::Private,
            'is_listed' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.courses.access.update', $course), [
                'user_ids' => [$student->id],
            ])
            ->assertRedirect();

        Notification::assertSentTo($student, CourseAccessGrantedNotification::class);

        $other = $this->approvedStudent('team@test.local');
        $team = Team::query()->create(['name' => 'T1']);
        $team->users()->attach($other->id);

        Notification::fake();

        $this->actingAs($admin)
            ->put(route('admin.courses.access.update', $course), [
                'user_ids' => [$student->id],
                'team_ids' => [$team->id],
            ])
            ->assertRedirect();

        Notification::assertSentTo($other, CourseAccessGrantedNotification::class);
    }

    public function test_removing_access_notifies_user_and_team(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $student = $this->approvedStudent();
        $teammate = $this->approvedStudent('mate@test.local');
        $course = Course::query()->create([
            'title' => 'Revoke Me',
            'slug' => 'revoke-me',
            'is_published' => true,
            'access_type' => CourseAccessType::Private,
            'is_listed' => true,
        ]);
        $team = Team::query()->create(['name' => 'Revoke Team']);
        $team->users()->attach($teammate->id);

        Enrollment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);
        $course->teams()->attach($team->id);

        $this->actingAs($admin)
            ->put(route('admin.courses.access.update', $course), [
                'user_ids' => [],
                'team_ids' => [],
            ])
            ->assertRedirect();

        Notification::assertSentTo($student, CourseAccessRevokedNotification::class);
        Notification::assertSentTo($teammate, CourseAccessRevokedNotification::class);
    }

    public function test_dashboard_shows_accessible_courses(): void
    {
        $student = $this->approvedStudent();
        $course = $this->publicCourse();
        Enrollment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $this->actingAs($student)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($course->title)
            ->assertDontSee(__('Course access'));
    }

    public function test_session_catchup_shows_once(): void
    {
        $student = $this->approvedStudent();
        $course = $this->publicCourse();
        $student->notify(new CourseAccessGrantedNotification($course, 'granted'));

        $this->actingAs($student)
            ->get(route('courses.index'))
            ->assertOk()
            ->assertSee(__('View notification'), false);

        $this->actingAs($student)
            ->get(route('courses.index'))
            ->assertOk()
            ->assertDontSee(__('View notification'), false);
    }
}
