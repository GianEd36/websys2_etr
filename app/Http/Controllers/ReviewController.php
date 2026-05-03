<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;

class ReviewController extends Controller
{
    public function store(Request $request, $id)
    {
        // Checks if critique already exist for the user
        $exists = Review::where('movie_id', $id)
                            ->where('user_id', auth()->id())
                            ->whereNull('parent_id') // <--- Add this constraint
                            ->exists();

        if ($exists) {
            return back()->withErrors(['message' => 'You have already critiqued this movie!']);
        }

        // Validate with custom messages
        $request->validate([
            'rating' => 'required|integer|min:1|max:10',
            'comment' => 'required|min:5|max:1000',
        ], [
            'rating.required' => 'A rating score is required for your critique.',
            'rating.integer' => 'The rating must be a whole number.',
            'comment.required' => 'Please provide a comment for your critique.',
            'comment.min' => 'Your critique must be at least 5 characters long.',
        ]);

        // Fetch TMDB data with error handling
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

        // Create a review
        Review::create([
            'movie_id' => $id,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name, 
            'rating' => $request->rating,
            'comment' => $request->comment,
            'movie_title' => $movieData['title'] ?? 'Unknown Movie',
            'movie_poster' => $movieData['poster_path'] ?? null,
            'parent_id' => null, // Explicitly set as null for main critiques
        ]);

        return redirect()->route('movie.details', $id)->with('success', 'Your critique has been published!');
    }
    public function destroy(Review $review)
    {
        //Ensure the logged-in user owns this review
        if (auth()->id() !== $review->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $review->delete();

        return back()->with('success', 'Review deleted successfully!');
    }
    public function reply(Request $request, Review $review)
    {
        $request->validate([
            'comment' => 'required|max:255',
        ]);

        Review::create([
            'user_id' => auth()->id(),
            'parent_id' => $review->id,
            'movie_id' => $review->movie_id, // Keep it linked to the same movie
            'movie_title' => $review->movie_title,
            'comment' => $request->comment,
            'rating' => null, // No rating for replies
        ]);

        return back()->with('success', 'Reply posted!');
    }
    public function upvote(Review $review)
    {
        $review->increment('upvotes');
        return back();
    }
    public function vote(Request $request, Review $review) {
        $type = $request->type; // 'up' or 'down'
        $userId = auth()->id();

        // Check if user already voted
        $existingVote = \App\Models\ReviewVote::where('user_id', $userId)
            ->where('review_id', $review->id)
            ->first();

        if ($existingVote) {
            if ($existingVote->type === $type) {
                // If they click the same button again, remove the vote (toggle)
                $existingVote->delete();
                $review->decrement($type === 'up' ? 'upvotes' : 'downvotes');
            } else {
                // If they change from up to down, update the counts
                $review->decrement($existingVote->type === 'up' ? 'upvotes' : 'downvotes');
                $existingVote->update(['type' => $type]);
                $review->increment($type === 'up' ? 'upvotes' : 'downvotes');
            }
        } else {
            // New vote
            \App\Models\ReviewVote::create([
                'user_id' => $userId,
                'review_id' => $review->id,
                'type' => $type
            ]);
            $review->increment($type === 'up' ? 'upvotes' : 'downvotes');
        }

        if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'upvotes' => $review->upvotes,
                    'downvotes' => $review->downvotes,
                    'status' => 'success'
                ]);
            }

        return back();
    }
}