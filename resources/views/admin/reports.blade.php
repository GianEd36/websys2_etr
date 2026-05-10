@extends('layouts.app')
@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-body mb-0">Reported Critiques</h2>
        <div class="btn-group">
            <a href="{{ route('admin.movies.stats') }}" class="btn btn-sm btn-primary">Statistics</a>
            <a href="{{ route('admin.appeals.index') }}" class="btn btn-sm btn-primary">View Appeals</a>
        </div>
    </div>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Reporter</th>
                <th>Critique Content</th>
                <th>Reason</th>
                <th>Author</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reports as $report)
            <tr data-report-id="{{ $report->id }}"
                data-href="{{ route('movie.details', $report->review->movie_id) }}?admin_return={{ urlencode(route('admin.reports.index')) }}#review-{{ $report->review->id }}">
                <td>{{ $report->user->name }}</td>
                <td><small>{{ Str::limit($report->review->comment, 50) }}</small></td>
                <td><small>{{ Str::limit($report->reason ?? 'No reason provided', 120) }}</small></td>
                <td>{{ $report->review->user->name }}</td>
                <td>
                    <div class="d-flex gap-2">
                        @php $reportedUser = $report->review->user; @endphp
                        @if($reportedUser && $reportedUser->is_banned)
                        <button class="btn btn-sm btn-success ajax-unban"
                            data-action="{{ route('admin.users.unban', $reportedUser->id) }}"
                            data-report-id="{{ $report->id }}">
                            Unban
                        </button>
                        @else
                        <button class="btn btn-sm btn-danger ajax-ban" 
                            data-action="{{ route('admin.users.ban', $report->review->user_id) }}"
                            data-report-id="{{ $report->id }}">
                            Ban User
                        </button>
                        @endif

                        <button class="btn btn-sm btn-outline-secondary ajax-dismiss"
                            data-action="{{ route('admin.reports.dismiss', $report->id) }}"
                            data-report-id="{{ $report->id }}">
                            Dismiss
                        </button>
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
    // Make rows clickable to jump to the movie page and highlight the critique for admin
    document.querySelectorAll('tr[data-href]').forEach(row => {
        row.style.cursor = 'pointer';
        row.addEventListener('click', function(e) {
            // Ignore clicks on buttons/inputs inside the row
            if (e.target.closest('button') || e.target.closest('a')) return;
            const href = this.dataset.href;
            if (href) window.location = href;
        });
    });

    const getToastContainer = () => {
        let tc = document.getElementById('toast-container');
        if (tc) return tc;
        tc = document.createElement('div');
        tc.id = 'toast-container';
        tc.className = 'toast-container position-fixed top-0 end-0 p-3';
        tc.style.zIndex = 200000;
        tc.style.pointerEvents = 'none';
        document.body.appendChild(tc);
        return tc;
    };

    const makeToast = (message, type = 'success', delay = 3000) => {
        const tc = getToastContainer();
        const toastEl = document.createElement('div');
        toastEl.className = `toast mb-2 align-items-center bg-${type} border-0`;
        toastEl.setAttribute('role','alert');
        toastEl.setAttribute('aria-live','assertive');
        toastEl.setAttribute('aria-atomic','true');
        toastEl.style.pointerEvents = 'auto';
        toastEl.innerHTML = `<div class="d-flex"><div class="toast-body text-white">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
        tc.appendChild(toastEl);
        const toast = new bootstrap.Toast(toastEl, { delay, autohide: true });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    };

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    document.querySelectorAll('.ajax-ban').forEach(btn => {
        btn.addEventListener('click', async function() {
            const action = this.dataset.action;
            const reportId = this.dataset.reportId;
            this.disabled = true;
            try {
                const res = await fetch(action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) throw res;
                const data = await res.json();
                makeToast(data.message || 'User banned.', 'success');
                // Optionally remove the report row
                if (reportId) {
                    const row = document.querySelector(`tr[data-report-id="${reportId}"]`);
                    if (row) row.remove();
                }
            } catch (err) {
                makeToast('Failed to ban user.', 'danger', 5000);
            } finally {
                this.disabled = false;
            }
        });
    });

    document.querySelectorAll('.ajax-unban').forEach(btn => {
        btn.addEventListener('click', async function() {
            const action = this.dataset.action;
            const reportId = this.dataset.reportId;
            this.disabled = true;
            try {
                const res = await fetch(action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) throw res;
                const data = await res.json();
                makeToast(data.message || 'User unbanned.', 'success');
                if (reportId) {
                    const row = document.querySelector(`tr[data-report-id="${reportId}"]`);
                    if (row) row.remove();
                }
            } catch (err) {
                makeToast('Failed to unban user.', 'danger', 5000);
            } finally {
                this.disabled = false;
            }
        });
    });

    document.querySelectorAll('.ajax-dismiss').forEach(btn => {
        btn.addEventListener('click', async function() {
            const action = this.dataset.action;
            const reportId = this.dataset.reportId;
            this.disabled = true;
            try {
                const body = new FormData();
                body.append('_method', 'DELETE');
                const res = await fetch(action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body
                });
                if (!res.ok) throw res;
                const data = await res.json();
                makeToast(data.message || 'Report dismissed.', 'success');
                if (reportId) {
                    const row = document.querySelector(`tr[data-report-id="${reportId}"]`);
                    if (row) row.remove();
                }
            } catch (err) {
                makeToast('Failed to dismiss report.', 'danger', 5000);
            } finally {
                this.disabled = false;
            }
        });
    });
});
</script>
@endpush