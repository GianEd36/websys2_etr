@extends('layouts.app')

@section('content')
<div class="container py-5 text-white">
    <!-- Movie Header -->
    <div class="row mb-5">
        <div class="col-md-4">
            @if($movie['poster_path'])
                <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" class="img-fluid rounded shadow-lg" alt="{{ $movie['title'] }}">
            @else
                <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="height: 450px;">No Poster</div>
            @endif
        </div>
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3 mb-2">
                <h1 class="fw-bold mb-0">{{ $movie['title'] }}</h1>
                <div class="d-flex align-items-center gap-3 text-muted small">
                    <span><i class="fas fa-eye me-1"></i> {{ number_format($viewCount) }} Views</span>
                    <span><i class="fas fa-star text-warning me-1"></i> {{ number_format($averageRating, 1) }} / 10</span>
                </div>
                @if(isset($averageRating) && $averageRating > 0)
                    <span class="badge bg-primary fs-5 shadow-sm">
                        <i class="fas fa-star text-warning me-1"></i> {{ number_format($averageRating, 1) }}
                    </span>
                @endif
            </div>
            <p class="text-muted fs-5">{{ $movie['tagline'] }}</p>
            <p class="mt-4">{{ $movie['overview'] }}</p>
            
            @if($trailer)
            <div class="mt-4">
                <button type="button" class="btn btn-outline-danger fw-bold" data-bs-toggle="modal" data-bs-target="#trailerModal">
                    <i class="fas fa-play me-2"></i> Watch Trailer
                </button>
            </div>

            <!-- Trailer Modal -->
            <div class="modal fade" id="trailerModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content bg-dark border-secondary">
                        <div class="modal-body p-0">
                            <div class="ratio ratio-16x9">
                                <iframe src="https://www.youtube.com/embed/{{ $trailer['key'] }}" allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="mt-4">
                <span class="text-muted">Release Date:</span> {{ $movie['release_date'] }}
            </div>

            <!-- Write a Review Section -->
            @auth
            <div class="card bg-dark border-secondary mt-5 shadow">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-pen me-2 text-primary"></i> Write a Critique</h5>
                    <form action="{{ route('reviews.store', $movie['id']) }}" method="POST">
                        @csrf
                        <input type="hidden" name="movie_title" value="{{ $movie['title'] }}">
                        <input type="hidden" name="movie_poster" value="{{ $movie['poster_path'] }}">
                        
                        <div class="mb-3">
                            <label class="small text-muted text-uppercase fw-bold">Rating (1-10)</label>
                            <input type="number" name="rating" class="form-control bg-dark text-white border-secondary w-25" min="1" max="10">
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted text-uppercase fw-bold">Your Comment</label>
                            <textarea name="comment" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="Share your thoughts..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Post Review</button>
                    </form>
                </div>
            </div>
            @else
            <div class="alert alert-info mt-5 bg-dark border-info text-info">
                Please <a href="{{ route('login') }}" class="fw-bold">Login</a> to leave a critique.
            </div>
            @endauth
        </div>
    </div>

    <hr class="my-5 opacity-25">

    <!-- User Critiques (Reddit Style) -->
    <div class="row">
        <div class="col-md-12">
            <h3 class="fw-bold mb-4">User Critiques</h3>
            
            @forelse($reviews->where('parent_id', null) as $review)
                <div class="card bg-dark border-0 shadow-sm mb-4" style="border-radius: 15px;">
                    <div class="card-body p-4 text-white">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="fw-bold mb-0 text-info">{{ $review->user->name }}</h6>
                                <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                            </div>
                            <span class="badge bg-warning text-dark">★ {{ $review->rating }}/10</span>
                        </div>
                        
                        <p class="mt-3 fs-5 mb-3">"{{ $review->comment }}"</p>

                        <!-- Interaction Bar -->
                        <div class="d-flex align-items-center gap-3 mt-2">
                            <!-- Unified Upvote -->
                                <form action="{{ route('reviews.vote', $review->id) }}" method="POST" class="vote-form d-inline">
                                    @csrf
                                    <input type="hidden" name="type" value="up">
                                    <button type="submit" class="btn btn-sm p-0 border-0 text-muted">
                                        <i class="fas fa-arrow-up {{ $review->votes->where('user_id', auth()->id())->where('type', 'up')->count() ? 'text-primary' : '' }}"></i> 
                                        <small id="upvotes-count-{{ $review->id }}">{{ $review->upvotes }}</small>
                                    </button>
                                </form>

                                <form action="{{ route('reviews.vote', $review->id) }}" method="POST" class="vote-form d-inline">
                                    @csrf
                                    <input type="hidden" name="type" value="down">
                                    <button type="submit" class="btn btn-sm p-0 border-0 text-muted">
                                        <i class="fas fa-arrow-down {{ $review->votes->where('user_id', auth()->id())->where('type', 'down')->count() ? 'text-danger' : '' }}"></i> 
                                        <small id="downvotes-count-{{ $review->id }}">{{ $review->downvotes }}</small>
                                    </button>
                                </form>

                            <button class="btn btn-sm btn-link text-decoration-none p-0 text-muted" type="button" data-bs-toggle="collapse" data-bs-target="#replyForm{{ $review->id }}">
                                <i class="fas fa-reply me-1"></i> Reply
                            </button>
                        </div>

                        <!-- Top-Level Reply Form -->
                        <div class="collapse mt-3" id="replyForm{{ $review->id }}">
                            <form action="{{ route('reviews.reply', $review->id) }}" method="POST">
                                @csrf
                                <div class="input-group">
                                    <input type="text" name="comment" class="form-control bg-dark text-white border-secondary" placeholder="Add a comment...">
                                    <button class="btn btn-primary" type="submit">Post</button>
                                </div>
                            </form>
                        </div>

                        <!-- Recursive Replies -->
                        @if($review->replies->count() > 0)
                            @foreach($review->replies as $reply)
                                <x-reply-item :reply="$reply" />
                            @endforeach
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <p class="text-muted">No one has critiqued this movie yet. Be the first!</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
    body { background-color: #121212; }
    .bg-dark { background-color: #1e1e1e !important; }
    .border-secondary { border-color: #333 !important; }
    .x-small { font-size: 0.75rem; }
</style>
@endsection
<!-- The upvotes downvotes script -->
<script>
document.addEventListener('submit', function(e) {
    if (e.target && e.target.classList.contains('vote-form')) {
        e.preventDefault();

        const form = e.target;
        const url = form.action;
        const formData = new FormData(form);
        const type = formData.get('type'); // 'up' or 'down'
        
        const urlParts = url.split('/');
        const reviewId = urlParts[urlParts.length - 2]; 

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // 1. Update the numbers
                const upLabel = document.getElementById(`upvotes-count-${reviewId}`);
                const downLabel = document.getElementById(`downvotes-count-${reviewId}`);
                if(upLabel) upLabel.innerText = data.upvotes;
                if(downLabel) downLabel.innerText = data.downvotes;

                // 2. Handle Icon Colors
                // Find both forms for this specific review/reply
                const parentContainer = form.closest('.d-flex');
                const upIcon = parentContainer.querySelector('input[value="up"]').parentElement.querySelector('i');
                const downIcon = parentContainer.querySelector('input[value="down"]').parentElement.querySelector('i');

                if (type === 'up') {
                    // Toggle blue for upvote, ensure downvote is gray
                    upIcon.classList.toggle('text-primary');
                    downIcon.classList.remove('text-danger');
                } else {
                    // Toggle red for downvote, ensure upvote is gray
                    downIcon.classList.toggle('text-danger');
                    upIcon.classList.remove('text-primary');
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }
});
</script>