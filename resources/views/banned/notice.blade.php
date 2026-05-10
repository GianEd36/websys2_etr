@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <h3 class="text-warning">Account Suspended</h3>
                    <p class="text-muted">Your account has been suspended for violating community guidelines. While suspended you cannot post critiques, vote, reply, or access authenticated areas.</p>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <p class="text-muted">If you think this was a mistake, you may submit an appeal below. Appeals are reviewed by the moderation team.</p>

                    <form method="POST" action="{{ route('banned.appeal') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Appeal message (optional)</label>
                            <textarea name="message" class="form-control" rows="5" placeholder="Explain why your account should be reinstated"></textarea>
                        </div>
                        <button class="btn btn-primary">Submit Appeal</button>
                    </form>

                    <hr class="my-3"/>
                    <p class="text-muted small">If your appeal is accepted you will be unbanned and your rights restored.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
