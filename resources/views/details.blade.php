@extends('layouts.app')

@section('content')
<div class="container py-5">
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
                    <div class="modal-content border-secondary">
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
            <div class="card border-secondary mt-5 shadow">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-pen me-2 text-primary"></i> Write a Critique</h5>
                    <form action="{{ route('reviews.store', $movie['id']) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="movie_title" value="{{ $movie['title'] }}">
                        <input type="hidden" name="movie_poster" value="{{ $movie['poster_path'] }}">
                        
                        <div class="mb-3">
                            <label class="small text-muted text-uppercase fw-bold">Rating (1-10)</label>
                            <input type="number" name="rating" class="form-control border-secondary w-25" min="1" max="10">
                        </div>
                        <!-- ADDED: Image Upload Field -->
                        <div class="mb-3">
                            <label class="small text-muted text-uppercase fw-bold"><i class="fas fa-image me-1"></i> Attach a Photo (Optional)</label>
                            <input type="file" name="image" class="form-control  border-secondary">
                            <div class="x-small text-muted mt-1">Supports JPG, PNG, WEBP (Max 2MB)</div>
                        </div>
                        <!-- In your Write a Critique section -->
                        <div class="mb-3 position-relative">
                            <label class="small text-muted text-uppercase fw-bold d-flex justify-content-between">
                                Your Comment
                                <button type="button" id="emoji-trigger" class="btn btn-sm p-0 text-primary border-0">
                                    <i class="far fa-smile fs-5"></i>
                                </button>
                            </label>
                            <textarea id="critique-comment" name="comment" class="form-control border-secondary" rows="3" placeholder="Share your thoughts..."></textarea>
                            
                            <!-- This container will now be moved around by JS -->
                            <div id="emoji-picker-container" class="emoji-picker-global shadow" style="display: none;"></div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Post Review</button>
                    </form>
                </div>
            </div>
            @else
            <div class="alert alert-info mt-5 border-info text-info">
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
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
                    <div class="card-body p-4 ">
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

                        <!-- Top-Level Reply Form (For parent critiques) -->
                        <!-- Find the Top-Level Reply Form in details.blade.php -->
                        <div class="collapse mt-3" id="replyForm{{ $review->id }}">
                            <form action="{{ route('reviews.reply', $review->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="card bg-body-tertiary border-secondary">
                                    <div class="card-body p-2">
                                        <textarea name="comment" class="form-control form-control-sm bg-transparent border-0 mb-2" 
                                                placeholder="Add a comment..." rows="2" required></textarea>
                                        
                                        <!-- ADDED: position-relative here -->
                                        <div class="d-flex justify-content-between align-items-center position-relative"> <!-- Add position-relative here -->
                                            <div class="d-flex gap-2">
                                                <label class="btn btn-sm btn-outline-secondary border-0 p-0 px-1 mb-0">
                                                    <i class="fas fa-image"></i>
                                                    <input type="file" name="image" class="d-none">
                                                </label>
                                                <button type="button" class="btn btn-sm btn-outline-secondary border-0 p-0 px-1 emoji-trigger-reply">
                                                    <i class="far fa-smile"></i>
                                                </button>
                                            </div>
                                            <button class="btn btn-primary btn-sm px-3" type="submit">Post</button>
                                        </div>
                                    </div>
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
document.addEventListener('DOMContentLoaded', async function() {
    let activeTextarea = null;
    const container = document.getElementById('emoji-picker-container');

    // Initialize the picker with automatic data loading
    const picker = new EmojiMart.Picker({
        theme: 'dark',
        set: 'native', // Use native emojis to avoid image loading issues
        onEmojiSelect: (emoji) => {
            if (activeTextarea) {
                const start = activeTextarea.selectionStart;
                const end = activeTextarea.selectionEnd;
                activeTextarea.value = activeTextarea.value.substring(0, start) + emoji.native + activeTextarea.value.substring(end);
                activeTextarea.focus();
                activeTextarea.setSelectionRange(start + emoji.native.length, start + emoji.native.length);
            }
        }
    });

    container.appendChild(picker);

    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('#emoji-trigger, .emoji-trigger-reply');
        
        if (trigger) {
            e.stopPropagation();
            e.preventDefault();

            const parentForm = trigger.closest('form');
            activeTextarea = parentForm.querySelector('textarea');

            // Find the anchor. It must be 'position-relative'
            const anchor = trigger.closest('.position-relative');

            if (container.style.display === 'block' && container.parentElement === anchor) {
                container.style.display = 'none';
            } else if (anchor) {
                anchor.appendChild(container);
                container.style.display = 'block';
            }
        } else if (!container.contains(e.target)) {
            container.style.display = 'none';
        }
    });
});
</script>

<style>
    .emoji-picker-global {
        position: absolute !important;
        z-index: 9999 !important;
        bottom: 100%; /* Positions it directly above the button/input */
        right: 0;
        margin-bottom: 10px;
        display: none;
    }

    /* Standardizes the context for main and reply forms */
    .position-relative { 
        position: relative !important; 
    }
</style>