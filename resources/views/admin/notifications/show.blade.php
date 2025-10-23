@extends('adminlte::page')

@section('title', 'Notification Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="{{ $notification->getIcon() }}"></i> Notification Details
        </h1>
        <div>
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            
            @if(in_array($notification->status, ['draft', 'scheduled']))
                <form action="{{ route('admin.notifications.send', $notification) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Send this notification now?')">
                        <i class="fas fa-paper-plane"></i> Send Now
                    </button>
                </form>
                
                <form action="{{ route('admin.notifications.cancel', $notification) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning" onclick="return confirm('Cancel this notification?')">
                        <i class="fas fa-ban"></i> Cancel
                    </button>
                </form>
            @endif
            
            @if($notification->status !== 'sending')
                <form action="{{ route('admin.notifications.destroy', $notification) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this notification?')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            @endif
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <!-- Main Content -->
    <div class="col-md-8">
        <!-- Notification Content -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-envelope"></i> Notification Content
                </h3>
                <div class="card-tools">
                    @switch($notification->status)
                        @case('draft')
                            <span class="badge badge-secondary badge-lg">
                                <i class="fas fa-edit"></i> Draft
                            </span>
                            @break
                        @case('scheduled')
                            <span class="badge badge-warning badge-lg">
                                <i class="fas fa-clock"></i> Scheduled
                            </span>
                            @break
                        @case('sending')
                            <span class="badge badge-info badge-lg">
                                <i class="fas fa-spinner fa-spin"></i> Sending
                            </span>
                            @break
                        @case('sent')
                            <span class="badge badge-success badge-lg">
                                <i class="fas fa-check"></i> Sent
                            </span>
                            @break
                        @case('failed')
                            <span class="badge badge-danger badge-lg">
                                <i class="fas fa-times"></i> Failed
                            </span>
                            @break
                        @case('cancelled')
                            <span class="badge badge-dark badge-lg">
                                <i class="fas fa-ban"></i> Cancelled
                            </span>
                            @break
                    @endswitch
                </div>
            </div>
            <div class="card-body">
                <!-- Notification Preview -->
                <div class="notification-preview mb-4">
                    <div class="alert alert-{{ $notification->getColor() }} alert-dismissible">
                        <div class="d-flex align-items-start">
                            <i class="{{ $notification->getIcon() }} mr-3 mt-1" style="font-size: 1.2em;"></i>
                            <div class="flex-grow-1">
                                <h5 class="alert-heading mb-2">
                                    {{ $notification->title }}
                                    @if($notification->priority === 'urgent')
                                        <span class="badge badge-danger ml-1">URGENT</span>
                                    @elseif($notification->priority === 'high')
                                        <span class="badge badge-warning ml-1">HIGH</span>
                                    @endif
                                    @if($notification->is_recurring)
                                        <span class="badge badge-info ml-1">
                                            <i class="fas fa-redo"></i> RECURRING
                                        </span>
                                    @endif
                                </h5>
                                <p class="mb-0">{{ $notification->message }}</p>
                                
                                @if($notification->action_url && $notification->action_text)
                                    <div class="mt-3">
                                        <a href="{{ $notification->action_url }}" target="_blank" 
                                           class="btn btn-outline-{{ $notification->getColor() }} btn-sm">
                                            {{ $notification->action_text }}
                                            <i class="fas fa-external-link-alt ml-1"></i>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-calendar-alt"></i>
                            @if($notification->sent_at)
                                Sent {{ $notification->sent_at->format('d/m/Y H:i') }} ({{ $notification->sent_at->diffForHumans() }})
                            @elseif($notification->scheduled_at)
                                Scheduled for {{ $notification->scheduled_at->format('d/m/Y H:i') }} ({{ $notification->scheduled_at->diffForHumans() }})
                            @else
                                Created {{ $notification->created_at->format('d/m/Y H:i') }} ({{ $notification->created_at->diffForHumans() }})
                            @endif
                        </small>
                    </div>
                </div>
                
                <!-- Notification Details -->
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-info-circle text-info"></i> Basic Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Type:</strong></td>
                                <td>
                                    <span class="badge badge-{{ $notification->getColor() }}">
                                        {{ ucfirst(str_replace('_', ' ', $notification->type)) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Priority:</strong></td>
                                <td>
                                    @switch($notification->priority)
                                        @case('urgent')
                                            <span class="badge badge-danger">Urgent</span>
                                            @break
                                        @case('high')
                                            <span class="badge badge-warning">High</span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">Normal</span>
                                    @endswitch
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Target:</strong></td>
                                <td>{{ ucfirst(str_replace('_', ' ', $notification->target_type)) }}</td>
                            </tr>
                            @if($notification->target_user_id)
                                <tr>
                                    <td><strong>Target User:</strong></td>
                                    <td>
                                        @if($notification->targetUser)
                                            <i class="fas fa-user"></i> {{ $notification->targetUser->name }}
                                            <br><small class="text-muted">{{ $notification->targetUser->email }}</small>
                                        @else
                                            <span class="text-muted">User not found</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h6><i class="fas fa-clock text-warning"></i> Timing & Recurring</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Created:</strong></td>
                                <td>{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @if($notification->scheduled_at)
                                <tr>
                                    <td><strong>Scheduled:</strong></td>
                                    <td>{{ $notification->scheduled_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endif
                            @if($notification->sent_at)
                                <tr>
                                    <td><strong>Sent:</strong></td>
                                    <td>{{ $notification->sent_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td><strong>Recurring:</strong></td>
                                <td>
                                    @if($notification->is_recurring)
                                        <i class="fas fa-check text-success"></i> Yes
                                        <br><small class="text-muted">{{ ucfirst($notification->recurring_frequency) }}</small>
                                        @if($notification->recurring_until)
                                            <br><small class="text-muted">Until: {{ Carbon\Carbon::parse($notification->recurring_until)->format('d/m/Y') }}</small>
                                        @endif
                                    @else
                                        <i class="fas fa-times text-muted"></i> No
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                @if($notification->action_url)
                    <hr>
                    <h6><i class="fas fa-link text-primary"></i> Action Information</h6>
                    <p>
                        <strong>Action URL:</strong> 
                        <a href="{{ $notification->action_url }}" target="_blank">
                            {{ $notification->action_url }} <i class="fas fa-external-link-alt"></i>
                        </a>
                    </p>
                    @if($notification->action_text)
                        <p><strong>Action Text:</strong> {{ $notification->action_text }}</p>
                    @endif
                @endif
            </div>
        </div>
        
        <!-- Delivery Statistics -->
        @if($notification->total_recipients > 0 || $notification->status !== 'draft')
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i> Delivery Statistics
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="info-box bg-info">
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Recipients</span>
                                    <span class="info-box-number">{{ number_format($notification->total_recipients) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="info-box bg-success">
                                <div class="info-box-content">
                                    <span class="info-box-text">Successfully Sent</span>
                                    <span class="info-box-number">{{ number_format($notification->total_sent) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="info-box bg-warning">
                                <div class="info-box-content">
                                    <span class="info-box-text">Read by Users</span>
                                    <span class="info-box-number">{{ number_format($notification->total_read) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="info-box bg-danger">
                                <div class="info-box-content">
                                    <span class="info-box-text">Failed to Send</span>
                                    <span class="info-box-number">{{ number_format($notification->total_failed) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($notification->total_recipients > 0)
                        <div class="progress mb-3">
                            @php
                                $sentPercentage = ($notification->total_sent / $notification->total_recipients) * 100;
                                $readPercentage = ($notification->total_read / $notification->total_recipients) * 100;
                                $failedPercentage = ($notification->total_failed / $notification->total_recipients) * 100;
                            @endphp
                            
                            <div class="progress-bar bg-success" style="width: {{ $sentPercentage }}%"
                                 title="Sent: {{ number_format($sentPercentage, 1) }}%">
                            </div>
                            <div class="progress-bar bg-warning" style="width: {{ $readPercentage }}%"
                                 title="Read: {{ number_format($readPercentage, 1) }}%">
                            </div>
                            <div class="progress-bar bg-danger" style="width: {{ $failedPercentage }}%"
                                 title="Failed: {{ number_format($failedPercentage, 1) }}%">
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <small class="text-muted">
                                Success Rate: <strong>{{ number_format($sentPercentage, 1) }}%</strong> |
                                Read Rate: <strong>{{ number_format($notification->total_recipients > 0 ? ($notification->total_read / $notification->total_sent) * 100 : 0, 1) }}%</strong>
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        @endif
        
        <!-- Recent Recipients (if sent) -->
        @if($notification->recipients()->exists())
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users"></i> Recent Recipients
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">{{ $notification->recipients()->count() }} total</span>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Status</th>
                                <th>Sent At</th>
                                <th>Read At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notification->recipients()->with('user')->orderBy('created_at', 'desc')->limit(20)->get() as $recipient)
                                <tr>
                                    <td>
                                        @if($recipient->user)
                                            <div>
                                                <strong>{{ $recipient->user->name }}</strong>
                                                <br><small class="text-muted">{{ $recipient->user->email }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">User deleted</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($recipient->failed_at)
                                            <span class="badge badge-danger">Failed</span>
                                        @elseif($recipient->read_at)
                                            <span class="badge badge-success">Read</span>
                                        @elseif($recipient->sent_at)
                                            <span class="badge badge-info">Sent</span>
                                        @else
                                            <span class="badge badge-secondary">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($recipient->sent_at)
                                            {{ $recipient->sent_at->format('d/m/Y H:i') }}
                                            <br><small class="text-muted">{{ $recipient->sent_at->diffForHumans() }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($recipient->read_at)
                                            {{ $recipient->read_at->format('d/m/Y H:i') }}
                                            <br><small class="text-muted">{{ $recipient->read_at->diffForHumans() }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    @if($notification->recipients()->count() > 20)
                        <div class="text-center p-3">
                            <small class="text-muted">
                                Showing 20 of {{ $notification->recipients()->count() }} recipients
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
    
    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bolt"></i> Quick Actions
                </h3>
            </div>
            <div class="card-body">
                @if(in_array($notification->status, ['draft', 'scheduled']))
                    <form action="{{ route('admin.notifications.send', $notification) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success btn-block"
                                onclick="return confirm('Send this notification immediately to {{ $notification->getTargetCount() }} recipients?')">
                            <i class="fas fa-paper-plane"></i> Send Now
                        </button>
                    </form>
                @endif
                
                @if($notification->is_recurring && $notification->status === 'sent')
                    <button class="btn btn-info btn-block mb-2" disabled>
                        <i class="fas fa-redo"></i> Recurring Active
                    </button>
                @endif
                
                @if(in_array($notification->status, ['draft', 'scheduled']))
                    <form action="{{ route('admin.notifications.cancel', $notification) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-block"
                                onclick="return confirm('Cancel this notification?')">
                            <i class="fas fa-ban"></i> Cancel
                        </button>
                    </form>
                @endif
                
                <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary btn-block mb-2">
                    <i class="fas fa-plus"></i> Create Similar
                </a>
                
                @if($notification->status !== 'sending')
                    <hr>
                    <form action="{{ route('admin.notifications.destroy', $notification) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block"
                                onclick="return confirm('Delete this notification permanently? This action cannot be undone.')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                @endif
            </div>
        </div>
        
        <!-- System Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> System Information
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td><i class="fas fa-hashtag text-muted"></i> ID:</td>
                        <td><code>{{ $notification->id }}</code></td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-calendar text-muted"></i> Created:</td>
                        <td>{{ $notification->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-edit text-muted"></i> Updated:</td>
                        <td>{{ $notification->updated_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    @if($notification->sent_at)
                        <tr>
                            <td><i class="fas fa-paper-plane text-muted"></i> Sent:</td>
                            <td>{{ $notification->sent_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td><i class="fas fa-database text-muted"></i> Recipients:</td>
                        <td>{{ number_format($notification->recipients()->count()) }} records</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .badge-lg {
        font-size: 0.9em;
        padding: 0.5em 0.8em;
    }
    
    .notification-preview {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        background: #f8f9fa;
    }
    
    .info-box {
        border-radius: 8px;
        margin-bottom: 15px;
    }
    
    .info-box-content {
        padding: 10px;
    }
    
    .progress {
        height: 25px;
    }
    
    .table td {
        padding: 0.5rem;
    }
    
    .card .table-borderless td {
        border: none;
        padding: 0.25rem 0.5rem;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Auto refresh if notification is sending
    @if($notification->status === 'sending')
        setTimeout(function() {
            window.location.reload();
        }, 3000);
    @endif
});
</script>
@stop