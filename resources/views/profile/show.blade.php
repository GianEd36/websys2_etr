@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm p-4" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); border-radius: 15px;">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" style="width: 80px; height: 80px; font-size: 2rem;">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    </div>
                    <div class="ms-4">
                        <h2 class="mb-1 fw-bold">{{ $user->name }}</h2>
                        <p class="text-muted mb-0">Member Since {{ $stats['member_since'] }}</p>
                        <span class="badge rounded-pill bg-info text-dark mt-2">
                            @if($stats['total_reviews'] >= 16) Master Reviewer 
                            @elseif($stats['total_reviews'] >= 6) Cinephile 
                            @else Novice Critic @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-5 text-center">
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm p-3" style="border-radius: 12px;">
                <h4 class="fw-bold mb-0">{{ $stats['total_reviews'] }}</h4>
                <small class="text-muted text-uppercase">Critiques</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm p-3" style="border-radius: 12px;">
                <h4 class="fw-bold mb-0 text-warning">★ {{ $stats['avg_rating'] }}</h4>
                <small class="text-muted text-uppercase">Avg Score</small>
            </div>
        </div>
    </div>

    <h3 class="mb-4 fw-bold">Recent Critiques</h3>
    <div class="row">
        @forelse($reviews as $review)
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                    <div class="row g-0">
                        <div class="col-4">
                            @if($review->movie_poster)
                                <img src="https://image.tmdb.org/t/p/w500{{ $review->movie_poster }}" class="img-fluid h-100 object-fit-cover" alt="{{ $review->movie_title }}">
                            @else
                                <div class="bg-secondary d-flex align-items-center justify-content-center h-100 text-white">No Poster</div>
                            @endif
                        </div>
                        <div class="col-8">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="card-title fw-bold mb-1">{{ $review->movie_title }}</h5>
                                    <span class="badge bg-dark">{{ $review->rating }}/10</span>
                                </div>
                                <p class="card-text text-muted small mt-2 italic">
                                    "{{ Str::limit($review->comment, 120) }}"
                                </p>
                                <a href="{{ route('movie.details', $review->movie_id) }}" class="btn btn-sm btn-outline-primary mt-2">View Movie</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <p class="text-muted">This user hasn't written any critiques yet.</p>
            </div>
        @endforelse
    </div>
</div>

<style>
    /* Custom flair to match your native styling preference */
    .object-fit-cover { object-fit: cover; }
    .card { transition: transform 0.2s ease-in-out; }
    .card:hover { transform: translateY(-5px); }
    .italic { font-style: italic; }
</style>
@endsection