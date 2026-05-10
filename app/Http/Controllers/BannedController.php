<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appeal;

class BannedController extends Controller
{
    public function notice()
    {
        return view('banned.notice');
    }

    public function appeal(Request $request)
    {
        $request->validate([ 'message' => 'nullable|string|max:2000' ]);

        Appeal::create([
            'user_id' => auth()->id(),
            'message' => $request->input('message')
        ]);

        return back()->with('success', 'Your appeal has been submitted. Our moderators will review it.');
    }
}
