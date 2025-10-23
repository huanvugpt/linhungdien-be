@extends('adminlte::page')

@section('title', 'Notification Statistics')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-chart-bar"></i> Notification Statistics
        </h1>
        <div>
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Notification
            </a>
        </div>
    </div>
@stop

@section('content')
<!-- Overview Cards -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($stats['total_notifications']) }}</h3>
                <p>Total Notifications</p>
            </div>
            <div class="icon">
                <i class="fas fa-bell"></i>
            </div>
            <a href="{{ route('admin.notifications.index') }}" class="small-box-footer">
                View All <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($stats['total_sent']) }}</h3>
                <p>Successfully Sent</p>
            </div>
            <div class="icon">
                <i class="fas fa-paper-plane"></i>
            </div>
            <a href="{{ route('admin.notifications.index', ['status' => 'sent']) }}" class="small-box-footer">
                View Sent <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($stats['total_recipients']) }}</h3>
                <p>Total Recipients</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="small-box-footer">
                All Recipients <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ number_format($stats['total_read']) }}</h3>
                <p>Messages Read</p>
            </div>
            <div class="icon">
                <i class="fas fa-eye"></i>
            </div>
            <div class="small-box-footer">
                Read Rate: {{ $stats['read_rate'] }}% <i class="fas fa-percentage"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <!-- Delivery Status Chart -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie"></i> Delivery Status Distribution
                </h3>
            </div>
            <div class="card-body">
                <canvas id="statusChart" style="height: 250px;"></canvas>
                
                <div class="mt-3">
                    <div class="row text-center">
                        <div class="col">
                            <span class="badge badge-success">{{ $stats['sent_notifications'] }} Sent</span>
                        </div>
                        <div class="col">
                            <span class="badge badge-warning">{{ $stats['scheduled_notifications'] }} Scheduled</span>
                        </div>
                        <div class="col">
                            <span class="badge badge-secondary">{{ $stats['draft_notifications'] }} Drafts</span>
                        </div>
                        <div class="col">
                            <span class="badge badge-danger">{{ $stats['failed_notifications'] }} Failed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Notification Types Chart -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-doughnut"></i> Notification Types
                </h3>
            </div>
            <div class="card-body">
                <canvas id="typesChart" style="height: 250px;"></canvas>
                
                <div class="mt-3">
                    <div class="progress-group">
                        @foreach($stats['by_type'] as $type => $count)
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small">{{ ucfirst(str_replace('_', ' ', $type)) }}</span>
                                <span class="badge badge-info">{{ $count }}</span>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-info" 
                                     style="width: {{ $stats['total_notifications'] > 0 ? ($count / $stats['total_notifications']) * 100 : 0 }}%">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Performance Metrics -->
<div class="row">
    <!-- Recent Activity -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i> Recent Activity (Last 30 Days)
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-toggle="dropdown">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="#" class="dropdown-item" onclick="updateChart('7')">Last 7 Days</a>
                        <a href="#" class="dropdown-item" onclick="updateChart('30')">Last 30 Days</a>
                        <a href="#" class="dropdown-item" onclick="updateChart('90')">Last 90 Days</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <canvas id="activityChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Top Performing Notifications -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-trophy"></i> Top Performing
                </h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($stats['top_notifications'] as $index => $notification)
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="me-auto">
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-{{ $index < 3 ? 'warning' : 'secondary' }} badge-pill me-2">
                                        {{ $index + 1 }}
                                    </span>
                                    <div>
                                        <h6 class="mb-1">{{ Str::limit($notification->title, 25) }}</h6>
                                        <p class="mb-1 small text-muted">
                                            <i class="{{ $notification->getIcon() }}"></i>
                                            {{ ucfirst($notification->type) }}
                                        </p>
                                        <small class="text-muted">{{ $notification->created_at->format('d/m/Y') }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-success">{{ $notification->total_read ?? 0 }} reads</span>
                                <br><small class="text-muted">{{ $notification->total_sent ?? 0 }} sent</small>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No notifications sent yet</p>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Statistics -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-table"></i> Detailed Statistics
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Delivery Metrics -->
                    <div class="col-md-4">
                        <h5><i class="fas fa-paper-plane text-success"></i> Delivery Metrics</h5>
                        <table class="table table-sm">
                            <tr>
                                <td>Success Rate:</td>
                                <td><strong>{{ $stats['success_rate'] }}%</strong></td>
                            </tr>
                            <tr>
                                <td>Failure Rate:</td>
                                <td><strong>{{ $stats['failure_rate'] }}%</strong></td>
                            </tr>
                            <tr>
                                <td>Avg Recipients per Notification:</td>
                                <td><strong>{{ $stats['avg_recipients'] }}</strong></td>
                            </tr>
                            <tr>
                                <td>Total Recipients Reached:</td>
                                <td><strong>{{ number_format($stats['total_recipients']) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Engagement Metrics -->
                    <div class="col-md-4">
                        <h5><i class="fas fa-eye text-info"></i> Engagement Metrics</h5>
                        <table class="table table-sm">
                            <tr>
                                <td>Read Rate:</td>
                                <td><strong>{{ $stats['read_rate'] }}%</strong></td>
                            </tr>
                            <tr>
                                <td>Total Reads:</td>
                                <td><strong>{{ number_format($stats['total_read']) }}</strong></td>
                            </tr>
                            <tr>
                                <td>Avg Time to Read:</td>
                                <td><strong>{{ $stats['avg_read_time'] ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <td>Most Active Hour:</td>
                                <td><strong>{{ $stats['most_active_hour'] ?? 'N/A' }}</strong></td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- System Performance -->
                    <div class="col-md-4">
                        <h5><i class="fas fa-server text-warning"></i> System Performance</h5>
                        <table class="table table-sm">
                            <tr>
                                <td>Notifications This Month:</td>
                                <td><strong>{{ number_format($stats['this_month']) }}</strong></td>
                            </tr>
                            <tr>
                                <td>Notifications Today:</td>
                                <td><strong>{{ number_format($stats['today']) }}</strong></td>
                            </tr>
                            <tr>
                                <td>Scheduled Pending:</td>
                                <td><strong>{{ number_format($stats['scheduled_notifications']) }}</strong></td>
                            </tr>
                            <tr>
                                <td>Recurring Active:</td>
                                <td><strong>{{ number_format($stats['recurring_active']) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .small-box .inner h3 {
        font-size: 2.2rem;
        font-weight: bold;
    }
    
    .badge-pill {
        border-radius: 50px;
        min-width: 20px;
        text-align: center;
    }
    
    .list-group-item {
        border-left: none;
        border-right: none;
    }
    
    .list-group-item:first-child {
        border-top: none;
    }
    
    .list-group-item:last-child {
        border-bottom: none;
    }
    
    .progress-sm {
        height: 5px;
    }
    
    .me-auto {
        margin-right: auto;
    }
    
    .me-2 {
        margin-right: 0.5rem;
    }
    
    .card-body canvas {
        max-height: 300px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Sent', 'Scheduled', 'Draft', 'Failed', 'Cancelled'],
            datasets: [{
                data: [
                    {{ $stats['sent_notifications'] }},
                    {{ $stats['scheduled_notifications'] }},
                    {{ $stats['draft_notifications'] }},
                    {{ $stats['failed_notifications'] }},
                    {{ $stats['cancelled_notifications'] ?? 0 }}
                ],
                backgroundColor: [
                    '#28a745',  // Success
                    '#ffc107',  // Warning
                    '#6c757d',  // Secondary
                    '#dc3545',  // Danger
                    '#343a40'   // Dark
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });
    
    // Notification Types Chart
    const typesCtx = document.getElementById('typesChart').getContext('2d');
    const typesChart = new Chart(typesCtx, {
        type: 'pie',
        data: {
            labels: [
                @foreach($stats['by_type'] as $type => $count)
                    '{{ ucfirst(str_replace("_", " ", $type)) }}',
                @endforeach
            ],
            datasets: [{
                data: [
                    @foreach($stats['by_type'] as $type => $count)
                        {{ $count }},
                    @endforeach
                ],
                backgroundColor: [
                    '#17a2b8',  // Info
                    '#ffc107',  // Warning
                    '#dc3545',  // Danger
                    '#28a745',  // Success
                    '#6f42c1',  // Purple
                    '#fd7e14'   // Orange
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true
                    }
                }
            }
        }
    });
    
    // Activity Chart (Line Chart)
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    const activityChart = new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: [
                @foreach($stats['daily_activity'] as $day => $count)
                    '{{ $day }}',
                @endforeach
            ],
            datasets: [{
                label: 'Notifications Sent',
                data: [
                    @foreach($stats['daily_activity'] as $day => $count)
                        {{ $count }},
                    @endforeach
                ],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#007bff',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            elements: {
                point: {
                    hoverRadius: 8
                }
            }
        }
    });
});

// Function to update activity chart with different time periods
function updateChart(days) {
    // This would typically make an AJAX call to get new data
    // For now, we'll just show an alert
    alert('Chart update for last ' + days + ' days - Feature coming soon!');
}
</script>
@stop