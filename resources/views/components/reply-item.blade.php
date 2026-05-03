@props(['reply'])

<div class="ms-4 mt-3 ps-3 border-start border-secondary">
    <div class="d-flex align-items-center gap-2 mt-1">
        <!-- Upvote -->
        <form action="{{ route('reviews.vote', $reply->id) }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="up">
            <button type="submit" class="btn btn-sm p-0 border-0 text-muted">
                <i class="fas fa-arrow-up {{ $reply->votes->where('user_id', auth()->id())->where('type', 'up')->count() ? 'text-primary' : '' }}"></i> 
                <small>{{ $reply->upvotes }}</small>
            </button>
        </form>

        <!-- Downvote -->
        <form action="{{ route('reviews.vote', $reply->id) }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="down">
            <button type="submit" class="btn btn-sm p-0 border-0 text-muted">
                <i class="fas fa-arrow-down {{ $reply->votes->where('user_id', auth()->id())->where('type', 'down')->count() ? 'text-danger' : '' }}"></i> 
                <small>{{ $reply->downvotes }}</small>
            </button>
        </form>

        <button class="btn btn-sm btn-link text-decoration-none p-0 x-small text-muted" 
                type="button" data-bs-toggle="collapse" data-bs-target="#replyForm{{ $reply->id }}">
            Reply
        </button>
    </div>
    <p class="small mb-1 opacity-75">{{ $reply->comment }}</p>

    <!-- Reply Toggle -->
    <button class="btn btn-sm btn-link text-decoration-none p-0 x-small text-muted" 
            type="button" data-bs-toggle="collapse" data-bs-target="#replyForm{{ $reply->id }}">
        Reply
    </button>

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

    <!-- RECURSION: The reply calls itself for its own replies -->
    @if($reply->replies->count() > 0)
        @foreach($reply->replies as $nestedReply)
            <x-reply-item :reply="$nestedReply" />
        @endforeach
    @endif
</div>