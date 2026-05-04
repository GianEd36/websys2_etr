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
                    <form action="{{ route('reviews.store', $movie['id']) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="movie_title" value="{{ $movie['title'] }}">
                        <input type="hidden" name="movie_poster" value="{{ $movie['poster_path'] }}">
                        
                        <div class="mb-3">
                            <label class="small text-muted text-uppercase fw-bold">Rating (1-10)</label>
                            <input type="number" name="rating" class="form-control bg-dark text-white border-secondary w-25" min="1" max="10">
                        </div>
                        <!-- ADDED: Image Upload Field -->
                        <div class="mb-3">
                            <label class="small text-muted text-uppercase fw-bold"><i class="fas fa-image me-1"></i> Attach a Photo (Optional)</label>
                            <input type="file" name="image" class="form-control bg-dark text-white border-secondary">
                            <div class="x-small text-muted mt-1">Supports JPG, PNG, WEBP (Max 2MB)</div>
                        </div>
                        <div class="mb-3 position-relative">
                            <label class="small text-muted text-uppercase fw-bold d-flex justify-content-between">
                                Your Comment
                                <button type="button" id="emoji-trigger" class="btn btn-sm p-0 text-primary border-0">
                                    <i class="far fa-smile fs-5"></i>
                                </button>
                            </label>
                            <textarea id="critique-comment" name="comment" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="Share your thoughts..."></textarea>
                            
                            <!-- The Picker Container (Hidden by default) -->
                            <div id="emoji-picker-container" class="position-absolute shadow" style="display: none; z-index: 1000; bottom: 100%;"></div>
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
                        <!-- Image upload-->
                        @if($review->image)
                            <div class="mt-3">
                                {{-- Verify this path specifically --}}
                                <img src="{{ asset('storage/' . $review->image) }}" 
                                    class="rounded border border-secondary shadow-sm img-fluid" 
                                    style="max-height: 400px; object-fit: cover;"
                                    alt="Critique Image">
                            </div>
                        @endif

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
@endsection
<!-- Load the library FIRST -->
<script src="https://cdn.jsdelivr.net/npm/emoji-mart@5.5.2/dist/browser.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pickerOptions = { 
        onEmojiSelect: (emoji) => {
            const textarea = document.getElementById('critique-comment');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            
            textarea.value = textarea.value.substring(0, start) + emoji.native + textarea.value.substring(end);
            
            textarea.focus();
            textarea.setSelectionRange(start + emoji.native.length, start + emoji.native.length);
        },
        theme: 'dark'
    }

    const picker = new EmojiMart.Picker(pickerOptions);
    const container = document.getElementById('emoji-picker-container');
    const trigger = document.getElementById('emoji-trigger');

    if (container && trigger) {
        container.appendChild(picker);

        trigger.addEventListener('click', (e) => {
            e.stopPropagation(); // Prevents the 'click outside' listener from closing it immediately
            container.style.display = container.style.display === 'none' ? 'block' : 'none';
        });

        // Close picker if clicking outside
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target) && e.target !== trigger) {
                container.style.display = 'none';
            }
        });
    }
});
</script>

<style>
    /* Ensure the picker stays on top and is visible against the dark background */
    #emoji-picker-container {
        right: 0;
        bottom: 40px; /* Positions it above the textarea */
        z-index: 2000;
    }
    
    /* Optional: Small tweak to the blue emoji icon to make it look like a button */
    #emoji-trigger {
        transition: transform 0.2s;
    }
    #emoji-trigger:hover {
        transform: scale(1.2);
        color: #0d6efd !important;
    }
</style>