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
}