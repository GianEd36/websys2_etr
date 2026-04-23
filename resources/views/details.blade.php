@extends('layouts.app')

@section('content')
<div class="container py-4">
    {{-- Success/Error Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Show Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        {{-- Poster --}}
        <div class="col-md-4 mb-4">
            <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" class="img-fluid rounded shadow-lg" alt="{{ $movie['title'] }}">
        </div>

        {{-- Info --}}
        <div class="col-md-8">
            {{-- Inside the details page header --}}
            <div class="d-flex align-items-center gap-3 mb-2">
                <h1 class="fw-bold mb-0">{{ $movie['title'] }}</h1>
                
                @if($averageRating)
                    <div class="text-center">
                        <span class="badge bg-primary fs-5 shadow-sm">
                            <i class="fas fa-user-check me-1"></i> {{ number_format($averageRating, 1) }}
                        </span>
                        <div class="text-uppercase text-primary fw-bold mt-1" style="font-size: 0.6rem; letter-spacing: 1px;">
                            KritikIt Score
                        </div>
                    </div>
                @endif
            </div>
            
            <p class="text-muted">{{ $movie['release_date'] }} | {{ $movie['runtime'] }} mins</p>
            <p class="lead text-body-secondary">{{ $movie['overview'] }}</p>
            
            <hr class="border-secondary my-4">

            {{-- Review Form --}}
            @auth
                <div class="card p-4 shadow-sm mb-5 bg-body-tertiary border-0">
                    <h5 class="fw-bold mb-3"><i class="fas fa-pen-nib me-2 text-primary"></i>Write a Critique</h5>
                    <form action="{{ route('reviews.store', $movie['id']) }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-uppercase">Rating (1-10)</label>
                                <input type="number" name="rating" class="form-control border-0 shadow-sm" min="1" max="10">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-uppercase">Your Comment</label>
                                <textarea name="comment" rows="3" class="form-control border-0 shadow-sm" placeholder="Share your thoughts..." ></textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary px-4 rounded-pill">Post Review</button>
                            </div>
                        </div>
                    </form>
                </div>
            @else
                <div class="alert alert-dark border-secondary mb-5">
                    <p class="mb-0">Want to rate this? <a href="{{ route('login') }}" class="text-primary fw-bold">Login</a> to join the critics.</p>
                </div>
            @endauth

            {{-- List of Reviews --}}
            <h4 class="fw-bold mb-4">Recent Critiques</h4>
            @forelse($localReviews as $review)
                <div class="card mb-3 bg-body-tertiary border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold text-primary">{{ $review->user_name }}</span>
                            <span class="badge bg-warning text-dark"><i class="fas fa-star me-1"></i>{{ $review->rating }}</span>
                        </div>
                        <p class="card-text fst-italic text-body-secondary">"{{ $review->comment }}"</p>
                        <small class="text-muted d-block text-end">{{ $review->created_at->diffForHumans() }}</small>
                    </div>
                </div>
            @empty
                <p class="text-muted text-center py-4">No reviews yet. Be the first to critic!</p>
            @endforelse
        </div>
    </div>
</div>
@endsection