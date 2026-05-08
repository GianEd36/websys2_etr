<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Review;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Review $review) {
        // Prevent duplicate reports from the same user
        $exists = Report::where('user_id', auth()->id())
                        ->where('review_id', $review->id)
                        ->exists();

        if (!$exists) {
            Report::create([
                'user_id' => auth()->id(),
                'review_id' => $review->id,
            ]);
        }

        return back()->with('success', 'Thank you. The critique has been reported for review.');
    }
}
