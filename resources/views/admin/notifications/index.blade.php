@extends('adminlte::page')

@section('title', 'Notifications Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-bell"></i> Notifications Management
        </h1>
        <div>
            <a href="{{ route('admin.notifications.stats') }}" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> Statistics
            </a>
            <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Notification
            </a>
        </div>
    </div>
@stop

@section('content')
<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-3">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total'] }}</h3>
                <p>Total Notifications</p>
            </div>
            <div class="icon">
                <i class="fas fa-bell"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['sent'] }}</h3>
                <p>Sent Successfully</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['scheduled'] }}</h3>
                <p>Scheduled</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['failed'] }}</h3>
                <p>Failed</p>
            </div>
            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list"></i> All Notifications
                </h3>
                <div class="card-tools">
                    <form method="GET" class="form-inline">
                        <div class="input-group input-group-sm mr-2">
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="sending" {{ request('status') == 'sending' ? 'selected' : '' }}>Sending</option>
                                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="stopped" {{ request('status') == 'stopped' ? 'selected' : '' }}>Stopped</option>
                            </select>
                        </div>
                        <div class="input-group input-group-sm mr-2">
                            <select name="type" class="form-control">
                                <option value="">All Types</option>
                                <option value="info" {{ request('type') == 'info' ? 'selected' : '' }}>Info</option>
                                <option value="warning" {{ request('type') == 'warning' ? 'selected' : '' }}>Warning</option>
                                <option value="error" {{ request('type') == 'error' ? 'selected' : '' }}>Error</option>
                                <option value="success" {{ request('type') == 'success' ? 'selected' : '' }}>Success</option>
                                <option value="system_announcement" {{ request('type') == 'system_announcement' ? 'selected' : '' }}>System Announcement</option>
                                <option value="daily_digest" {{ request('type') == 'daily_digest' ? 'selected' : '' }}>Daily Digest</option>
                            </select>
                        </div>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" placeholder="Search notifications..." 
                                   value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                @if($notifications->count() > 0)
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Notification</th>
                                <th>Type</th>
                                <th>Target</th>
                                <th>Status</th>
                                <th>Schedule</th>
                                <th>Recipients</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notifications as $notification)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-start">
                                            <i class="{{ $notification->getIcon() }} text-{{ $notification->getColor() }} mr-2 mt-1"></i>
                                            <div>
                                                <h6 class="mb-1">
                                                    <a href="{{ route('admin.notifications.show', $notification) }}" class="text-dark">
                                                        {{ Str::limit($notification->title, 40) }}
                                                    </a>
                                                    @if($notification->is_recurring)
                                                        <i class="fas fa-redo text-info" title="Recurring"></i>
                                                    @endif
                                                    @if($notification->priority === 'urgent')
                                                        <span class="badge badge-danger badge-sm">URGENT</span>
                                                    @elseif($notification->priority === 'high')
                                                        <span class="badge badge-warning badge-sm">HIGH</span>
                                                    @endif
                                                </h6>
                                                <p class="text-muted mb-0 small">{{ Str::limit($notification->message, 60) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $notification->getColor() }}">
                                            {{ ucfirst(str_replace('_', ' ', $notification->type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ ucfirst(str_replace('_', ' ', $notification->target_type)) }}</span>
                                    </td>
                                    <td>
                                        @switch($notification->status)
                                            @case('draft')
                                                <span class="badge badge-secondary">
                                                    <i class="fas fa-edit"></i> Draft
                                                </span>
                                                @break
                                            @case('scheduled')
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-clock"></i> Scheduled
                                                </span>
                                                @break
                                            @case('sending')
                                                <span class="badge badge-info">
                                                    <i class="fas fa-spinner fa-spin"></i> Sending
                                                </span>
                                                @break
                                            @case('sent')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check"></i> Sent
                                                </span>
                                                @break
                                            @case('failed')
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-times"></i> Failed
                                                </span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge badge-dark">
                                                    <i class="fas fa-ban"></i> Cancelled
                                                </span>
                                                @break
                                            @case('stopped')
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-stop"></i> Stopped
                                                </span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($notification->scheduled_at)
                                            <div>
                                                {{ $notification->scheduled_at->format('d/m/Y H:i') }}
                                                <br><small class="text-muted">{{ $notification->scheduled_at->diffForHumans() }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">Not scheduled</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($notification->total_recipients > 0)
                                            <div class="text-center">
                                                <strong>{{ number_format($notification->total_sent) }}</strong> / 
                                                <span class="text-muted">{{ number_format($notification->total_recipients) }}</span>
                                                <br><small class="text-success">{{ $notification->total_read }} read</small>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.notifications.show', $notification) }}" class="btn btn-info btn-sm" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if(in_array($notification->status, ['draft', 'scheduled']))
                                                <form action="{{ route('admin.notifications.send', $notification) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm" title="Send Now"
                                                            onclick="return confirm('Send this notification now?')">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if(in_array($notification->status, ['draft', 'scheduled']))
                                                <form action="{{ route('admin.notifications.cancel', $notification) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-warning btn-sm" title="Cancel"
                                                            onclick="return confirm('Cancel this notification?')">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if($notification->status === 'sending')
                                                <form action="{{ route('admin.notifications.stop', $notification) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Stop Sending"
                                                            onclick="return confirm('Stop sending this notification? Pending recipients will be marked as failed.')">
                                                        <i class="fas fa-stop"></i> Stop
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if($notification->status !== 'sending')
                                                <form action="{{ route('admin.notifications.destroy', $notification) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete"
                                                            onclick="return confirm('Delete this notification? This action cannot be undone.')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-bell fa-3x text-muted mb-3"></i>
                        <h4>No Notifications Found</h4>
                        <p class="text-muted">Start engaging with your users by creating your first notification!</p>
                        <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Your First Notification
                        </a>
                    </div>
                @endif
            </div>
            @if($notifications->hasPages())
                <div class="card-footer">
                    {{ $notifications->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .badge-sm {
        font-size: 0.6em;
        padding: 0.2em 0.4em;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .small-box .inner h3 {
        font-size: 2.2rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .card-tools .form-inline .form-control {
        width: auto;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Auto refresh for sending status
    const hasSendingNotifications = {{ $notifications->where('status', 'sending')->count() > 0 ? 'true' : 'false' }};
    
    if (hasSendingNotifications) {
        // Refresh page every 5 seconds if there are sending notifications
        setTimeout(function() {
            window.location.reload();
        }, 5000);
    }
});
</script>
@stop