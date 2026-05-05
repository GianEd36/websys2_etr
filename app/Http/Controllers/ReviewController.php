<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function store(Request $request, $id)
    {
        // 1. Checks if critique already exists for the user
        $exists = Review::where('movie_id', $id)
                            ->where('user_id', auth()->id())
                            ->whereNull('parent_id') 
                            ->exists();

        if ($exists) {
            return back()->withErrors(['message' => 'You have already critiqued this movie!']);
        }

        // 2. Updated Validation to include the image
        $request->validate([
            'rating' => 'required|integer|min:1|max:10',
            'comment' => 'required|min:5|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Added image validation
        ], [
            'rating.required' => 'A rating score is required for your critique.',
            'rating.integer' => 'The rating must be a whole number.',
            'comment.required' => 'Please provide a comment for your critique.',
            'comment.min' => 'Your critique must be at least 5 characters long.',
            'image.image' => 'The file must be an image (jpeg, png, jpg, or webp).',
        ]);

        // 3. Handle the Image Upload before creating the record
        $imagePath = null;
        if ($request->hasFile('image')) {
            // Stores in storage/app/public/reviews
            $imagePath = $request->file('image')->store('reviews', 'public');
        }

        // 4. Fetch TMDB data with error handling (Existing logic)
        try {
            $token = config('services.tmdb.token');
            $response = Http::withToken($token)->timeout(5)->get("https://api.themoviedb.org/3/movie/{$id}");
            
            if ($response->failed()) {
                throw new \Exception('Could not connect to movie database.');
            }
            
            $movieData = $response->json();
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Unable to verify movie details. Please try again later.']);
        }

        // 5. Create the review with the image path included
        Review::create([
            'movie_id' => $id,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name, 
            'rating' => $request->rating,
            'comment' => $request->comment,
            'image' => $imagePath, // New field
            'movie_title' => $movieData['title'] ?? 'Unknown Movie',
            'movie_poster' => $movieData['poster_path'] ?? null,
            'parent_id' => null, 
        ]);

        return redirect()->route('movie.details', $id)->with('success', 'Your critique has been published!');
    }
    public function destroy(Review $review)
    {
        //Ensure the logged-in user owns this review
        if (auth()->id() !== $review->user_id) {
            abort(403, 'Unauthorized action.');
        }
        // NEW: Delete physical file if it exists
        if ($review->image) {
            // Since we're storing in 'reviews' on the 'public' disk
            \Illuminate\Support\Facades\Storage::disk('public')->delete($review->image);
        }
        $review->delete();

        return back()->with('success', 'Review and associated image deleted successfully!');
    }
    public function reply(Request $request, Review $review)
    {
        $request->validate([
            'comment' => 'required|max:255',
            'image' => 'nullable|image|max:2048'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('reviews', 'public');
        }

        Review::create([
            'user_id' => auth()->id(),
            'parent_id' => $review->id,
            'movie_id' => $review->movie_id, // Keep it linked to the same movie
            'movie_title' => $review->movie_title,
            'comment' => $request->comment,
            'image' => $imagePath, // Save the reply image path
            'rating' => null, // No rating for replies
        ]);

        return back()->with('success', 'Reply posted!');
    }
    public function upvote(Review $review)
    {
        $review->increment('upvotes');
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'new_count' => ($type === 'up') ? $review->upvotes : $review->downvotes
            ]);
        }
        return back();
    }
public function vote(Request $request, Review $review) {
    $type = $request->type; // 'up' or 'down'
    $userId = auth()->id();
    $voted = false; // Track if a vote exists at the end

    $existingVote = \App\Models\ReviewVote::where('user_id', $userId)
        ->where('review_id', $review->id)
        ->first();

    if ($existingVote) {
        if ($existingVote->type === $type) {
            $existingVote->delete();
            $review->decrement($type === 'up' ? 'upvotes' : 'downvotes');
            $voted = false; // Vote was removed
        } else {
            $review->decrement($existingVote->type === 'up' ? 'upvotes' : 'downvotes');
            $existingVote->update(['type' => $type]);
            $review->increment($type === 'up' ? 'upvotes' : 'downvotes');
            $voted = true; // Vote was changed
        }
    } else {
        \App\Models\ReviewVote::create([
            'user_id' => $userId,
            'review_id' => $review->id,
            'type' => $type
        ]);
        $review->increment($type === 'up' ? 'upvotes' : 'downvotes');
        $voted = true; // New vote added
    }

    if ($request->ajax() || $request->wantsJson()) {
        return response()->json([
            'success' => true,
            'voted' => $voted,
            'new_count' => ($type === 'up') ? $review->upvotes : $review->downvotes,
            'upvotes' => $review->upvotes,
            'downvotes' => $review->downvotes
        ]);
    }

    return back();
}
}