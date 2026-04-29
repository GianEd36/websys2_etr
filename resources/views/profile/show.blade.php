@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Profile Header Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm p-4 profile-header-card">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white profile-avatar">
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

    <!-- Stats Bar -->
    <div class="row g-3 mb-5 text-center">
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm p-3 stat-card">
                <h4 class="fw-bold mb-0">{{ $stats['total_reviews'] }}</h4>
                <small class="text-muted text-uppercase small-label">Critiques</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm p-3 stat-card">
                <h4 class="fw-bold mb-0 text-warning">★ {{ $stats['avg_rating'] }}</h4>
                <small class="text-muted text-uppercase small-label">Avg Score</small>
            </div>
        </div>
    </div>

    <h3 class="mb-4 fw-bold">Recent Critiques</h3>
    <div class="row">
        @forelse($reviews->where('parent_id', null) as $review)
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm border-0 critique-card">
                    <div class="row g-0">
                        <!-- Movie Poster Side -->
                        <div class="col-md-2">
                            @if($review->movie_poster)
                                <img src="https://image.tmdb.org/t/p/w500{{ $review->movie_poster }}" class="img-fluid h-100 object-fit-cover poster-img" alt="{{ $review->movie_title }}">
                            @else
                                <div class="bg-secondary d-flex align-items-center justify-content-center h-100 text-white">No Poster</div>
                            @endif
                        </div>
                        
                        <!-- Content Side -->
                        <div class="col-md-10">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="card-title fw-bold mb-1">{{ $review->movie_title }}</h5>
                                    <span class="badge bg-dark rating-badge">{{ $review->rating }}/10</span>
                                </div>
                                <p class="card-text text-muted mt-2 review-text">
                                    "{{ $review->comment }}"
                                </p>

                                <!-- Social Interaction Bar -->
                                <hr class="my-3 opacity-10">
                                <div class="d-flex align-items-center gap-3">
                                    <button class="btn btn-sm btn-link text-decoration-none p-0 reply-btn" type="button" data-bs-toggle="collapse" data-bs-target="#replyForm{{ $review->id }}">
                                        <i class="fas fa-reply me-1"></i> Reply
                                    </button>
                                    <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                </div>

                                <!-- Reply Form (Collapsible) -->
                                <div class="collapse mt-3" id="replyForm{{ $review->id }}">
                                    <form action="{{ route('reviews.reply', $review->id) }}" method="POST" class="bg-light p-3 rounded">
                                        @csrf
                                        <div class="input-group">
                                            <input type="text" name="comment" class="form-control" placeholder="What did they get right/wrong?">
                                            <button class="btn btn-primary" type="submit">Post</button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Display Threaded Replies -->
                                @if($review->replies->count() > 0)
                                    <div class="mt-4 ms-2 border-start ps-4 reply-thread">
                                        @foreach($review->replies as $reply)
                                            <div class="mb-3">
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="fw-bold small me-2">{{ $reply->user->name }}</span>
                                                    <small class="text-muted x-small">{{ $reply->created_at->diffForHumans() }}</small>
                                                </div>
                                                <p class="small mb-0 text-secondary">{{ $reply->comment }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <p class="text-muted">This critic has not shared any thoughts yet.</p>
            </div>
        @endforelse
    </div>
</div>

<style>
    .profile-header-card { background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); border-radius: 15px; }
    .profile-avatar { width: 80px; height: 80px; font-size: 2rem; }
    .stat-card { border-radius: 12px; }
    .small-label { font-size: 0.7rem; letter-spacing: 1px; }
    .critique-card { border-radius: 12px; overflow: hidden; transition: transform 0.2s; }
    .poster-img { min-height: 180px; }
    .review-text { font-style: italic; line-height: 1.6; }
    .rating-badge { font-size: 0.9rem; padding: 0.5em 0.8em; }
    .reply-btn { color: #6c757d; font-weight: 500; font-size: 0.85rem; }
    .reply-btn:hover { color: #0d6efd; }
    .reply-thread { border-color: #dee2e6 !important; }
    .x-small { font-size: 0.75rem; }
</style>
@endsection