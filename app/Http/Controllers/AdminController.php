<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    //
    public function index() {
        // Eager load everything to see the reporter, the critique, and the critique's author
        $reports = Report::with(['user', 'review.user'])->latest()->get();
        return view('admin.reports', compact('reports'));
    }

    public function banUser(User $user) {
        $user->update(['is_banned' => true]);
        // Optionally delete all their reviews too
        $user->reviews()->delete();
        return back()->with('success', 'User has been banned.');
    }
}
