@props(['reply'])

<div class="ms-4 mt-3 ps-3 border-start border-secondary">
    <div class="d-flex align-items-center gap-2">
        <span class="fw-bold small text-info">{{ $reply->user->name }}</span>
        <small class="text-muted" style="font-size: 0.7rem;">{{ $reply->created_at->diffForHumans() }}</small>
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