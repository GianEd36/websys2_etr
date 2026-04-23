@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fw-bold mb-0">My Reviews</h1>
            <p class="text-secondary">You have shared your thoughts on {{ $reviews->count() }} movies.</p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-plus me-2"></i>Find Movies
        </a>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        @forelse($reviews as $review)
            <div class="col">
                <div class="card h-100 border-0 shadow-sm bg-body-tertiary movie-card">
                    {{-- Movie Poster --}}
                    <div class="position-relative">
                        <img src="https://image.tmdb.org/t/p/w500{{ $review->movie_poster }}" 
                             class="card-img-top rounded-top" 
                             alt="{{ $review->movie_title }}"
                             style="height: 350px; object-fit: cover;">
                        
                        {{-- Rating Badge Overlay --}}
                        <span class="position-absolute top-0 end-0 m-2 badge bg-warning text-dark fw-bold shadow-sm">
                            <i class="fas fa-star me-1"></i>{{ $review->rating }}/10
                        </span>
                    </div>

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title h6 fw-bold text-truncate mb-1" title="{{ $review->movie_title }}">
                            {{ $review->movie_title }}
                        </h5>
                        
                        <p class="card-text small text-secondary mb-3">
                            <i class="far fa-clock me-1"></i>{{ $review->created_at->diffForHumans() }}
                        </p>

                        <div class="bg-body p-2 rounded mb-3 flex-grow-1">
                            <p class="card-text small fst-italic mb-0">
                                "{{ Str::limit($review->comment, 100) }}"
                            </p>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ url('/movie/'.$review->movie_id) }}" class="btn btn-sm btn-outline-primary w-100">
                                Details
                            </a>
                            {{-- Optional: Delete or Edit buttons --}}
                            <form action="{{ route('reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('Delete this review?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-film fa-4 light-text opacity-25" style="font-size: 5rem;"></i>
                </div>
                <h3 class="text-secondary">No reviews yet</h3>
                <p class="text-muted">Start your journey as a critic by reviewing your favorite movies.</p>
                <a href="{{ route('home') }}" class="btn btn-primary mt-2">Browse Movies</a>
            </div>
        @endforelse
    </div>
</div>
@endsection