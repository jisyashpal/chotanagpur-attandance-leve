@extends('layouts.admin')

@section('title', 'Activity Logs')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Activity Logs</span>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Actor</label>
                    <select name="actor_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($actors as $actor)
                            <option value="{{ $actor->id }}" @selected(request('actor_id') == $actor->id)>{{ $actor->name }} ({{ $actor->role }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Role</label>
                    <select name="actor_role" class="form-select">
                        <option value="">All</option>
                        @foreach (['admin','faculty','staff','student'] as $role)
                            <option value="{{ $role }}" @selected(request('actor_role') === $role)>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Action</label>
                    <select name="action" class="form-select">
                        <option value="">All</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Description">
                </div>
                <div class="col-12 text-end">
                    <button class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.activity_logs.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">When</th>
                            <th>Who</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Subject</th>
                            <th class="pe-4">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                        <tr>
                            <td class="ps-4 small">{{ $log->created_at?->format('d M Y H:i') }}</td>
                            <td>
                                {{ $log->actor->name ?? 'System' }}
                                <div class="small text-muted">{{ $log->actor_role }}</div>
                            </td>
                            <td><span class="badge bg-primary-subtle text-primary">{{ $log->action }}</span></td>
                            <td>{{ $log->description }}</td>
                            <td class="small">
                                @if ($log->subject_type)
                                    {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="pe-4">
                                @if ($log->properties)
                                    <details>
                                        <summary class="small text-primary" style="cursor:pointer;">View</summary>
                                        <pre class="small mb-0 mt-2" style="max-width:320px;white-space:pre-wrap;">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No activity logs found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($logs->hasPages())
        <div class="card-footer bg-white">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
@endsection
