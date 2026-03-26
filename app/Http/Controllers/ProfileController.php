<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Profile $profile): View
    {
        $profile->load('user');

        return view('profiles.show', compact('profile'));
    }

    public function edit(Request $request): View
    {
        $profile = $request->user()->profile;

        return view('profiles.edit', compact('profile'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->profile;

        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'handle' => ['required', 'string', 'regex:/^[a-zA-Z0-9_]{2,32}$/', 'unique:profiles,handle,'.$profile->id],
            'bio' => ['nullable', 'string', 'max:2000'],
        ]);

        $profile->update($data);

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
