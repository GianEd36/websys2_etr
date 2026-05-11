@props(['reply'])

<div class="ms-4 mt-3 ps-3 border-start border-secondary">
    <div class="mb-1">
        <span class="fw-bold text-info small">{{ $reply->user->name }}</span>
        <span class="text-muted x-small ms-2">{{ $reply->created_at->diffForHumans() }}</span>
    </div>

    <p class="small mb-1 text-white">{{ $reply->comment }}</p>

    <!-- NEW: Display the Reply Image if it exists -->
    @if($reply->image)
        <div class="mt-2 mb-2">
            <img src="{{ asset('storage/' . $reply->image) }}" 
                 class="rounded border border-secondary img-fluid shadow-sm" 
                 style="max-height: 200px; object-fit: cover;" 
                 alt="Reply Image">
        </div>
    @endif

    <div class="d-flex align-items-center gap-3 mt-1">
        <form action="{{ route('reviews.vote', $reply->id) }}" method="POST" class="vote-form d-inline" data-id="{{ $reply->id }}">
            @csrf
            <input type="hidden" name="type" value="up">
            <button type="submit" class="btn btn-sm p-0 border-0 text-muted">
                <i class="fas fa-arrow-up {{ $reply->votes->where('user_id', auth()->id())->where('type', 'up')->count() ? 'text-primary' : '' }}"></i> 
                <small id="upvotes-count-{{ $reply->id }}">{{ $reply->upvotes }}</small>
            </button>
        </form>

        <form action="{{ route('reviews.vote', $reply->id) }}" method="POST" class="vote-form d-inline" data-id="{{ $reply->id }}">
            @csrf
            <input type="hidden" name="type" value="down">
            <button type="submit" class="btn btn-sm p-0 border-0 text-muted">
                <i class="fas fa-arrow-down {{ $reply->votes->where('user_id', auth()->id())->where('type', 'down')->count() ? 'text-danger' : '' }}"></i> 
                <small id="downvotes-count-{{ $reply->id }}">{{ $reply->downvotes }}</small>
            </button>
        </form>

        <button class="btn btn-sm btn-link text-decoration-none p-0 x-small text-muted" 
                type="button" data-bs-toggle="collapse" data-bs-target="#replyForm{{ $reply->id }}">
            <i class="fas fa-reply me-1"></i>Reply
        </button>
        <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-danger open-report-modal ms-3"
                data-action="{{ route('reviews.report', $reply->id) }}">
            <i class="fas fa-flag me-1"></i>Report
        </button>
    </div>

    <!-- UPDATED: Nested Reply Form with Image & Emoji support -->
    <div class="collapse mt-2" id="replyForm{{ $reply->id }}">
        <form action="{{ route('reviews.reply', $reply->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card bg-body-tertiary border-secondary">
                <div class="card-body p-2">
                    <textarea name="comment" class="form-control form-control-sm bg-transparent border-0 text-white mb-2" 
                              placeholder="Reply to {{ $reply->user->name }}..." rows="2" required></textarea>
                    
                    <!-- Inside reply-item.blade.php, update the reply form footer -->
                    <div class="d-flex justify-content-between align-items-center position-relative">
                        <div class="d-flex gap-2">
                            <label class="btn btn-sm btn-outline-secondary border-0 p-0 px-1">
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

    @if($reply->replies->count() > 0)
        @foreach($reply->replies as $nestedReply)
            <x-reply-item :reply="$nestedReply" />
        @endforeach
    @endif
</div>