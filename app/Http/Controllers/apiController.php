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

        // Always fetch genres so the view doesn't crash
        $genresResponse = Http::withToken($token)->get("https://api.themoviedb.org/3/genre/movie/list");
        $genres = $genresResponse->json()['genres'] ?? [];

        // Our filter
        $sortBy = $request->query('sort_by');

        if ($sortBy) {
            $movieIds = [];

            if ($sortBy === 'views') {
                $movieIds = \App\Models\MovieView::orderBy('views', 'desc')->take(20)->pluck('movie_id');
            } 
            elseif ($sortBy === 'top_rated') {
                // Get movie IDs ordered by average rating (only for root critiques)
                $movieIds = \App\Models\Review::whereNull('parent_id')
                    ->select('movie_id', \DB::raw('AVG(rating) as avg_rating'))
                    ->groupBy('movie_id')
                    ->having(\DB::raw('COUNT(*)'), '>=', 1) // Optional: change 1 to 3 for better quality
                    ->orderBy('avg_rating', 'desc')
                    ->take(20)
                    ->pluck('movie_id');
            }
            elseif ($sortBy === 'critiqued') {
                // Count unique reviews per movie (where parent_id is null)
                $movieIds = \App\Models\Review::whereNull('parent_id')
                    ->select('movie_id', \DB::raw('count(*) as total'))
                    ->groupBy('movie_id')
                    ->orderBy('total', 'desc')
                    ->take(20)
                    ->pluck('movie_id');
            } 
            elseif ($sortBy === 'engaged') {
                // Count replies/conversations per movie (where parent_id is NOT null)
                $movieIds = \App\Models\Review::whereNotNull('parent_id')
                    ->select('movie_id', \DB::raw('count(*) as total'))
                    ->groupBy('movie_id')
                    ->orderBy('total', 'desc')
                    ->take(20)
                    ->pluck('movie_id');
            }

            $movies = [];
            foreach ($movieIds as $id) {
                $movieData = Http::withToken($token)->get("https://api.themoviedb.org/3/movie/{$id}")->json();
                if (isset($movieData['id'])) { $movies[] = $movieData; }
            }

            return view('homepage', [
                'movies' => $movies,
                'genres' => $genres,
                'currentPage' => 1,
                'totalPages' => 1
            ]);
        }

        $response = Http::withToken($token)->get("https://api.themoviedb.org/3/movie/popular", ['page' => $page,]);
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
    public function showDetails($id)
    {
        // Fetch movie AND videos (trailers) in one go
        $movie = Http::withToken(config('services.tmdb.token'))
            ->get("https://api.themoviedb.org/3/movie/{$id}?append_to_response=videos")
            ->json();

        // Find the YouTube Trailer from the results
        $trailer = collect($movie['videos']['results'])->firstWhere('type', 'Trailer') 
           ?? collect($movie['videos']['results'])->first(); // Fallback to any video if no trailer

        // Movie Views Logic
        $viewRecord = \App\Models\MovieView::firstOrCreate(['movie_id' => $id]);
        $viewRecord->increment('views');
        $viewCount = $viewRecord->views; // Pass to the view

        $reviews = \App\Models\Review::where('movie_id', $id)
            ->with(['user', 'votes', 'replies.user', 'replies.votes', 'replies.replies.votes']) // Add 'votes' here
            ->whereNull('parent_id') 
            ->latest()
            ->get();

        $averageRating = $reviews->avg('rating');

        return view('details', compact('movie', 'reviews', 'averageRating', 'trailer', 'viewCount'));
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
