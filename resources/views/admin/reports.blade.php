@extends('layouts.app')
@section('content')
<div class="container py-5">
    <h2 class="text-white mb-4">Reported Critiques</h2>
    <table class="table table-dark table-hover">
        <thead>
            <tr>
                <th>Reporter</th>
                <th>Critique Content</th>
                <th>Author</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reports as $report)
            <tr>
                <td>{{ $report->user->name }}</td>
                <td><small>{{ Str::limit($report->review->comment, 50) }}</small></td>
                <td>{{ $report->review->user->name }}</td>
                <td>
                    <div class="d-flex gap-2">
                        <form action="{{ route('admin.users.ban', $report->review->user_id) }}" method="POST">
                            @csrf
                            <button class="btn btn-sm btn-danger">Ban User</button>
                        </form>
                        <form action="{{ route('admin.reports.dismiss', $report->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-secondary">Dismiss</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection