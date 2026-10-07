<?php

namespace Tests\Feature;

use App\Enums\CourseAccessType;
use App\Enums\PurchaseStatus;
use App\Enums\UserRole;
use App\Models\Course;
use App\Models\CoursePurchase;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseAccessModelTest extends TestCase
{
    use RefreshDatabase;

    private function approvedStudent(string $email = 'student@test.local'): User
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
            'email' => 'admin-access@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
        ]);
        $user->forceFill(['approved_at' => now()])->save();

        return $user;
    }

    private function courseWithLesson(array $attrs = []): array
    {
        $course = Course::query()->create(array_merge([
            'title' => 'Access Course',
            'slug' => 'access-course-'.uniqid(),
            'description' => 'Outline',
            'is_published' => true,
            'access_type' => CourseAccessType::Public,
            'price_mmk' => 10000,
            'is_listed' => true,
        ], $attrs));

        $module = Module::query()->create([
            'course_id' => $course->id,
            'sort_order' => 1,
            'title' => 'M1',
        ]);

        $lesson = Lesson::query()->create([
            'module_id' => $module->id,
            'sort_order' => 1,
            'title' => 'Lesson One',
            'video_driver' => 'youtube',
            'video_ref' => 'dQw4w9WgXcQ',
            'duration_seconds' => 120,
            'documentation_markdown' => null,
        ]);

        return [$course, $lesson];
    }

    public function test_public_purchase_pending_then_approve_unlocks_lesson(): void
    {
        Storage::fake('local');
        [$course, $lesson] = $this->courseWithLesson();
        $student = $this->approvedStudent();
        $admin = $this->admin();

        $this->actingAs($student)
            ->get(route('courses.show', $course))
            ->assertOk()
            ->assertSee(__('Buy'))
            ->assertSee('Lesson One')
            ->assertDontSee(route('lessons.show', $lesson), false);

        $this->actingAs($student)
            ->post(route('courses.purchase', $course), [
                'slip' => UploadedFile::fake()->image('slip.jpg'),
            ])
            ->assertRedirect(route('courses.show', $course));

        $purchase = CoursePurchase::query()->first();
        $this->assertNotNull($purchase);
        $this->assertTrue($purchase->status === PurchaseStatus::Pending);

        $this->actingAs($student)->get(route('lessons.show', $lesson))->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.purchases.approve', $purchase))
            ->assertRedirect(route('admin.purchases.index'));

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $this->actingAs($student)->get(route('lessons.show', $lesson))->assertOk();
    }

    public function test_private_course_cannot_be_purchased(): void
    {
        Storage::fake('local');
        [$course] = $this->courseWithLesson([
            'access_type' => CourseAccessType::Private,
            'price_mmk' => null,
            'is_listed' => true,
        ]);
        $student = $this->approvedStudent();

        $this->actingAs($student)
            ->get(route('courses.show', $course))
            ->assertOk()
            ->assertSee(__('Private'))
            ->assertDontSee(__('Buy'));

        $this->actingAs($student)
            ->post(route('courses.purchase', $course), [
                'slip' => UploadedFile::fake()->image('slip.jpg'),
            ])
            ->assertNotFound();

        $this->assertSame(0, CoursePurchase::query()->count());
    }

    public function test_team_grant_unlocks_members(): void
    {
        [$course, $lesson] = $this->courseWithLesson([
            'access_type' => CourseAccessType::Private,
            'price_mmk' => null,
        ]);
        $student = $this->approvedStudent();
        $outsider = $this->approvedStudent('out@test.local');
        $team = Team::query()->create(['name' => 'Cohort A']);
        $team->users()->attach($student->id);
        $course->teams()->attach($team->id);

        $this->actingAs($student)->get(route('lessons.show', $lesson))->assertOk();
        $this->actingAs($outsider)->get(route('lessons.show', $lesson))->assertForbidden();
    }

    public function test_unlisted_private_404_for_strangers(): void
    {
        [$course] = $this->courseWithLesson([
            'access_type' => CourseAccessType::Private,
            'price_mmk' => null,
            'is_listed' => false,
        ]);
        $student = $this->approvedStudent();

        $this->get(route('courses.show', $course))->assertNotFound();
        $this->actingAs($student)->get(route('courses.show', $course))->assertNotFound();
        $this->get(route('courses.index'))->assertOk()->assertDontSee($course->title);
    }

    public function test_catalog_lists_public_and_listed_private_only(): void
    {
        [$public] = $this->courseWithLesson([
            'title' => 'Public Listed',
            'slug' => 'public-listed',
            'access_type' => CourseAccessType::Public,
            'price_mmk' => 5000,
        ]);
        [$privateListed] = $this->courseWithLesson([
            'title' => 'Private Listed',
            'slug' => 'private-listed',
            'access_type' => CourseAccessType::Private,
            'price_mmk' => null,
            'is_listed' => true,
        ]);
        [$privateHidden] = $this->courseWithLesson([
            'title' => 'Private Hidden',
            'slug' => 'private-hidden',
            'access_type' => CourseAccessType::Private,
            'price_mmk' => null,
            'is_listed' => false,
        ]);

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee('Public Listed')
            ->assertSee('Private Listed')
            ->assertSee(__('Private'))
            ->assertDontSee('Private Hidden');

        unset($public, $privateListed, $privateHidden);
    }

    public function test_admin_can_attach_team_on_course_edit(): void
    {
        $admin = $this->admin();
        [$course] = $this->courseWithLesson([
            'access_type' => CourseAccessType::Private,
            'price_mmk' => null,
        ]);
        $team = Team::query()->create(['name' => 'Team Grant']);

        $this->actingAs($admin)
            ->put(route('admin.courses.access.update', $course), [
                'team_ids' => [$team->id],
            ])
            ->assertRedirect();

        $this->assertTrue($course->fresh()->teams->contains($team));
    }

    public function test_personal_enrollment_still_grants_access(): void
    {
        [$course, $lesson] = $this->courseWithLesson([
            'access_type' => CourseAccessType::Private,
            'price_mmk' => null,
            'is_listed' => false,
        ]);
        $student = $this->approvedStudent();
        Enrollment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $this->actingAs($student)->get(route('courses.show', $course))->assertOk();
        $this->actingAs($student)->get(route('lessons.show', $lesson))->assertOk();
    }
}
