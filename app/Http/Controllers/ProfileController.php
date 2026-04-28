<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\User;
use App\Models\Review;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        
        // Fetch user's reviews with movie details
        $reviews = Review::where('user_id', $id)
            ->latest()
            ->get();

        // Calculate some fun stats
        $stats = [
            'total_reviews' => $reviews->count(),
            'avg_rating' => number_format($reviews->avg('rating'), 1),
            'member_since' => $user->created_at->format('M Y'),
        ];

        return view('profile.show', compact('user', 'reviews', 'stats'));
    }
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
