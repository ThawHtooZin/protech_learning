<?php

namespace Tests\Feature\Phase2;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileSocialTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_avatar_and_social_links(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['approved_at' => now()]);
        Profile::query()->create(['user_id' => $user->id, 'handle' => 'learner1', 'display_name' => 'Learner']);

        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $this->actingAs($user)
            ->put(route('profiles.update'), [
                'display_name' => 'Learner Pro',
                'handle' => 'learner1',
                'bio' => 'Hello',
                'location' => 'Yangon',
                'social_github' => 'https://github.com/learner1',
                'avatar' => $file,
            ])
            ->assertRedirect();

        $profile = $user->profile->fresh();
        $this->assertEquals('Yangon', $profile->location);
        $this->assertEquals('https://github.com/learner1', $profile->social_links['github']);
        $this->assertNotNull($profile->avatar_path);
        Storage::disk('public')->assertExists($profile->avatar_path);
    }

    public function test_invalid_avatar_is_rejected(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['approved_at' => now()]);
        Profile::query()->create(['user_id' => $user->id, 'handle' => 'x', 'display_name' => 'X']);

        $this->actingAs($user)
            ->put(route('profiles.update'), [
                'display_name' => 'X',
                'handle' => 'x',
                'avatar' => UploadedFile::fake()->create('doc.pdf', 100),
            ])
            ->assertSessionHasErrors('avatar');
    }
}
