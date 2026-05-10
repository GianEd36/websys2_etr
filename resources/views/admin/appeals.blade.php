@extends('layouts.app')
@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white">Appeals</h2>
        <div class="btn-group">
            <a href="{{ route('admin.movies.stats') }}" class="btn btn-sm btn-outline-light">Statistics</a>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-light">Back to Reports</a>
        </div>
    </div>

    <table class="table table-dark table-hover">
        <thead>
            <tr>
                <th>User</th>
                <th>Message</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($appeals as $appeal)
            <tr data-appeal-id="{{ $appeal->id }}">
                <td>{{ $appeal->user->name }}</td>
                <td><small>{{ Str::limit($appeal->message ?? '—', 120) }}</small></td>
                <td>{{ ucfirst($appeal->status) }}</td>
                <td>{{ $appeal->created_at->diffForHumans() }}</td>
                <td>
                    <div class="d-flex gap-2">
                        @if($appeal->status === 'pending')
                        <button class="btn btn-sm btn-success ajax-accept" data-action="{{ route('admin.appeals.accept', $appeal->id) }}" data-appeal-id="{{ $appeal->id }}">Accept</button>
                        <button class="btn btn-sm btn-danger ajax-deny" data-action="{{ route('admin.appeals.deny', $appeal->id) }}" data-appeal-id="{{ $appeal->id }}">Deny</button>
                        @else
                        <span class="text-muted small">No actions</span>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const makeToast = (msg, type='success') => {
        const tc = document.getElementById('toast-container') || (() => {
            const el = document.createElement('div'); el.id='toast-container'; el.className='toast-container position-fixed top-0 end-0 p-3'; el.style.zIndex=200000; el.style.pointerEvents='none'; document.body.appendChild(el); return el;
        })();
        const toastEl = document.createElement('div');
        toastEl.className = `toast mb-2 align-items-center bg-${type} border-0`;
        toastEl.style.pointerEvents = 'auto';
        toastEl.innerHTML = `<div class="d-flex"><div class="toast-body text-white">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
        tc.appendChild(toastEl);
        const toast = new bootstrap.Toast(toastEl, { delay: 3000, autohide: true });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    };

    document.querySelectorAll('.ajax-accept').forEach(btn => {
        btn.addEventListener('click', async function(){
            this.disabled = true;
            try {
                const res = await fetch(this.dataset.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw res;
                const data = await res.json();
                makeToast(data.message || 'Appeal accepted', 'success');
                const row = document.querySelector(`tr[data-appeal-id="${this.dataset.appealId}"]`);
                if (row) row.remove();
            } catch (e) {
                makeToast('Failed to accept appeal', 'danger');
            } finally { this.disabled = false; }
        });
    });

    document.querySelectorAll('.ajax-deny').forEach(btn => {
        btn.addEventListener('click', async function(){
            this.disabled = true;
            try {
                const res = await fetch(this.dataset.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw res;
                const data = await res.json();
                makeToast(data.message || 'Appeal denied', 'success');
                const row = document.querySelector(`tr[data-appeal-id="${this.dataset.appealId}"]`);
                if (row) row.remove();
            } catch (e) {
                makeToast('Failed to deny appeal', 'danger');
            } finally { this.disabled = false; }
        });
    });
});
</script>
@endpush
