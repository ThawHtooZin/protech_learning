<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Profile;
use App\Services\LessonAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private LessonAccessService $lessonAccess,
    ) {}

    public function show(Profile $profile): View
    {
        $profile->load('user');

        $courseProgress = collect();
        if ($profile->user) {
            $courses = $profile->user->courses()
                ->where('is_published', true)
                ->orderBy('title')
                ->get();

            $courseProgress = $courses->map(fn (Course $course) => [
                'course' => $course,
                'percent' => $this->lessonAccess->courseCompletionPercent($profile->user, $course),
            ]);
        }

        return view('profiles.show', compact('profile', 'courseProgress'));
    }

    public function edit(Request $request): View
    {
        $profile = $request->user()->profile;
        $social = $profile->social_links ?? [];

        return view('profiles.edit', compact('profile', 'social'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->profile;

        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'handle' => ['required', 'string', 'regex:/^[a-zA-Z0-9_]{2,32}$/', 'unique:profiles,handle,'.$profile->id],
            'bio' => ['nullable', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:120'],
            'social_github' => ['nullable', 'url', 'max:255'],
            'social_linkedin' => ['nullable', 'url', 'max:255'],
            'social_x' => ['nullable', 'url', 'max:255'],
            'social_website' => ['nullable', 'url', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['sometimes', 'boolean'],
        ]);

        $socialLinks = array_filter([
            'github' => $data['social_github'] ?? null,
            'linkedin' => $data['social_linkedin'] ?? null,
            'x' => $data['social_x'] ?? null,
            'website' => $data['social_website'] ?? null,
        ]);

        $profile->update([
            'display_name' => $data['display_name'],
            'handle' => $data['handle'],
            'bio' => $data['bio'] ?? null,
            'location' => $data['location'] ?? null,
            'social_links' => $socialLinks ?: null,
        ]);

        if ($request->boolean('remove_avatar') && $profile->avatar_path) {
            Storage::disk('public')->delete($profile->avatar_path);
            $profile->update(['avatar_path' => null]);
        }

        if ($request->hasFile('avatar')) {
            if ($profile->avatar_path) {
                Storage::disk('public')->delete($profile->avatar_path);
            }

            $path = $request->file('avatar')->store(
                'avatars/'.$user->id,
                'public'
            );
            $profile->update(['avatar_path' => $path]);
        }

        return redirect()->route('profiles.show', $profile)->with('status', __('Profile updated.'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->forceFill([
            'password' => $request->input('password'),
        ])->save();

        return redirect()->route('profiles.edit')->with('status', __('Password changed.'));
    }
}
