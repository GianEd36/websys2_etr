@props(['reply'])

<div class="ms-4 mt-3 ps-3 border-start border-secondary">
    <!-- Add this Header Section -->
    <div class="mb-1">
        <span class="fw-bold text-info small">{{ $reply->user->name }}</span>
        <span class="text-muted x-small ms-2">{{ $reply->created_at->diffForHumans() }}</span>
    </div>

    <p class="small mb-1 text-white">{{ $reply->comment }}</p>

    <div class="d-flex align-items-center gap-3 mt-1">
        <!-- Upvote -->
        <!-- Example for the Upvote Form -->
        <form action="{{ route('reviews.vote', $reply->id) }}" method="POST" class="vote-form d-inline">
            @csrf
            <input type="hidden" name="type" value="up">
            <button type="submit" class="btn btn-sm p-0 border-0 text-muted">
                <i class="fas fa-arrow-up {{ $reply->votes->where('user_id', auth()->id())->where('type', 'up')->count() ? 'text-primary' : '' }}"></i> 
                <!-- Add an ID to this small tag -->
                <small id="upvotes-count-{{ $reply->id }}">{{ $reply->upvotes }}</small>
            </button>
        </form>

        <!-- Do the same for Downvote -->
        <form action="{{ route('reviews.vote', $reply->id) }}" method="POST" class="vote-form d-inline">
            @csrf
            <input type="hidden" name="type" value="down">
            <button type="submit" class="btn btn-sm p-0 border-0 text-muted">
                <i class="fas fa-arrow-down {{ $reply->votes->where('user_id', auth()->id())->where('type', 'down')->count() ? 'text-danger' : '' }}"></i> 
                <small id="downvotes-count-{{ $reply->id }}">{{ $reply->downvotes }}</small>
            </button>
        </form>

        <!-- Reply Button (Only need one) -->
        <button class="btn btn-sm btn-link text-decoration-none p-0 x-small text-muted" 
                type="button" data-bs-toggle="collapse" data-bs-target="#replyForm{{ $reply->id }}">
            <i class="fas fa-reply me-1"></i>Reply
        </button>
    </div>

    <!-- Nested Reply Form -->
    <div class="collapse mt-2" id="replyForm{{ $reply->id }}">
        <form action="{{ route('reviews.reply', $reply->id) }}" method="POST">
            @csrf
            <div class="input-group input-group-sm">
                <input type="text" name="comment" class="form-control bg-dark text-white border-secondary" placeholder="Reply to {{ $reply->user->name }}..." required>
                <button class="btn btn-primary" type="submit">Post</button>
            </div>
        </form>
    </div>

    <!-- RECURSION -->
    @if($reply->replies->count() > 0)
        @foreach($reply->replies as $nestedReply)
            <x-reply-item :reply="$nestedReply" />
        @endforeach
    @endif
</div>