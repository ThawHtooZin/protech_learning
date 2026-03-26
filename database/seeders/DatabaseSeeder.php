<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\ForumCategory;
use App\Models\Profile;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Safe to run multiple times: skips if demo admin already exists.
     * For a clean reset: php artisan migrate:fresh --seed
     *
     * Courses and quizzes are not seeded — add content via Admin or your own seeders.
     */
    public function run(): void
    {
        if (User::query()->where('email', 'admin@gmail.com')->exists()) {
            $this->command?->warn('Seed user already present (admin@gmail.com). Skipping seed. Use migrate:fresh --seed to reset.');

            return;
        }

        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'approved_at' => now(),
        ]);
        $admin->forceFill(['approved_by_user_id' => $admin->id])->save();
        Profile::query()->create([
            'user_id' => $admin->id,
            'handle' => 'admin',
            'display_name' => 'Admin',
        ]);

        ForumCategory::query()->create([
            'name' => 'General',
            'slug' => 'general',
            'sort_order' => 1,
        ]);

        Tag::query()->create(['name' => 'Laravel', 'slug' => 'laravel']);
        Tag::query()->create(['name' => 'Help', 'slug' => 'help']);
    }
}
