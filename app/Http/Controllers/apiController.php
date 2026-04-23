<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Review;

class apiController extends Controller
{
    public function showMovies(Request $request): View
    {
        $token = config('services.tmdb.token');
        $page = $request->get('page', 1);

        // Fetch the genres list so the card loop can find the names
        $genres = Http::withToken($token)
            ->get("https://api.themoviedb.org/3/genre/movie/list")
            ->json()['genres'] ?? [];

        $response = Http::withToken($token)->get("https://api.themoviedb.org/3/movie/popular", [
            'page' => $page,
        ]);

        $data = $response->json();

        return view('homepage', [
            'movies' => $data['results'],
            'genres' => $genres,
            'currentPage' => $data['page'],
            'totalPages' => $data['total_pages']
        ]);
    }
    public function search(Request $request): View
    {
        $query = $request->input('query');
        $token = config('services.tmdb.token');
        $page = $request->get('page', 1); // Get current search page

        if (!$query) {
            return redirect()->route('home');
        }

        $response = Http::withToken($token)->get("https://api.themoviedb.org/3/search/movie", [
            'query' => $query,
            'language' => 'en-US',
            'page' => $page,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return view('homepage', [
                'movies' => $data['results'],
                'currentPage' => $data['page'],
                'totalPages' => $data['total_pages']
            ]);
        }

        abort(404);
    }
    public function byGenre(Request $request, $id): View
    {
        $token = config('services.tmdb.token');

        $response = Http::withToken($token)->get("https://api.themoviedb.org/3/discover/movie", [
            'with_genres' => $id,
            'page' => $request->get('page', 1),
        ]);

        $data = $response->json();

        return view('homepage', [
            'movies' => $data['results'],
            'currentPage' => $data['page'],
            'totalPages' => $data['total_pages']
        ]);
    }
    public function showDetails($id) {
        $token = config('services.tmdb.token');

        // Fetch TMDB data
        $movie = Http::withToken($token)->get("https://api.themoviedb.org/3/movie/{$id}")->json();

        // Fetch local reviews
        $localReviews = Review::where('movie_id', $id)->latest()->get();

        // Calculate Average (returns null if no reviews yet)
        $averageRating = Review::where('movie_id', $id)->avg('rating');

        return view('details', compact('movie', 'localReviews', 'averageRating'));
    }
    public function storeReview(Request $request, $id) {
        $request->validate([
            'rating' => 'required|integer|min:1|max:10',
            'comment' => 'required|min:5'
        ]);

        \App\Models\Review::create([
            'movie_id' => $id,
            'user_name' => auth()->check() ? auth()->user()->name : 'Guest User', 
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'Review posted!');
    }
}
