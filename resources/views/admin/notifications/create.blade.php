@extends('adminlte::page')

@section('title', 'Create Notification')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-plus"></i> Create New Notification
        </h1>
        <div>
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <form action="{{ route('admin.notifications.store') }}" method="POST" id="notification-form">
            @csrf
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit"></i> Notification Content
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Title -->
                    <div class="form-group">
                        <label for="title" class="required">Title</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title') }}" maxlength="255" required>
                        @error('title')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">
                            Clear and concise notification title (max 255 characters)
                        </small>
                    </div>
                    
                    <!-- Message -->
                    <div class="form-group">
                        <label for="message" class="required">Message</label>
                        <textarea class="form-control @error('message') is-invalid @enderror" 
                                  id="message" name="message" rows="5" maxlength="1000" required>{{ old('message') }}</textarea>
                        @error('message')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">
                            <span id="message-count">0</span>/1000 characters
                        </small>
                    </div>
                    
                    <!-- Action URL (Optional) -->
                    <div class="form-group">
                        <label for="action_url">Action URL (Optional)</label>
                        <input type="url" class="form-control @error('action_url') is-invalid @enderror" 
                               id="action_url" name="action_url" value="{{ old('action_url') }}"
                               placeholder="https://example.com/page">
                        @error('action_url')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">
                            Optional link users can click to take action
                        </small>
                    </div>
                    
                    <!-- Action Text (Optional) -->
                    <div class="form-group">
                        <label for="action_text">Action Text (Optional)</label>
                        <input type="text" class="form-control @error('action_text') is-invalid @enderror" 
                               id="action_text" name="action_text" value="{{ old('action_text') }}"
                               placeholder="Read More, View Details, etc.">
                        @error('action_text')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cog"></i> Settings
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Type -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type" class="required">Type</label>
                                <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="info" {{ old('type') == 'info' ? 'selected' : '' }}>
                                        📝 Info - General information
                                    </option>
                                    <option value="warning" {{ old('type') == 'warning' ? 'selected' : '' }}>
                                        ⚠️ Warning - Important notice
                                    </option>
                                    <option value="error" {{ old('type') == 'error' ? 'selected' : '' }}>
                                        ❌ Error - Problem notification
                                    </option>
                                    <option value="success" {{ old('type') == 'success' ? 'selected' : '' }}>
                                        ✅ Success - Good news
                                    </option>
                                    <option value="system_announcement" {{ old('type') == 'system_announcement' ? 'selected' : '' }}>
                                        📢 System Announcement
                                    </option>
                                    <option value="daily_digest" {{ old('type') == 'daily_digest' ? 'selected' : '' }}>
                                        📊 Daily Digest
                                    </option>
                                </select>
                                @error('type')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Priority -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="priority" class="required">Priority</label>
                                <select class="form-control @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                    <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>
                                        Normal Priority
                                    </option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>
                                        High Priority
                                    </option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>
                                        🚨 Urgent Priority
                                    </option>
                                </select>
                                @error('priority')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Target Type -->
                    <div class="form-group">
                        <label for="target_type" class="required">Target Audience</label>
                        <select class="form-control @error('target_type') is-invalid @enderror" 
                                id="target_type" name="target_type" required>
                            <option value="">Select Target</option>
                            <option value="all_users" {{ old('target_type') == 'all_users' ? 'selected' : '' }}>
                                👥 All Users
                            </option>
                            <option value="specific_user" {{ old('target_type') == 'specific_user' ? 'selected' : '' }}>
                                👤 Specific User
                            </option>
                            <option value="user_group" {{ old('target_type') == 'user_group' ? 'selected' : '' }}>
                                👨‍👩‍👧‍👦 User Group (Future)
                            </option>
                            <option value="new_users" {{ old('target_type') == 'new_users' ? 'selected' : '' }}>
                                🆕 New Users (Last 30 days)
                            </option>
                            <option value="active_users" {{ old('target_type') == 'active_users' ? 'selected' : '' }}>
                                ⚡ Active Users (Last 7 days)
                            </option>
                        </select>
                        @error('target_type')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <!-- Target User ID (for specific user) -->
                    <div class="form-group" id="target-user-group" style="display: none;">
                        <label for="target_user_id">Target User</label>
                        <select class="form-control @error('target_user_id') is-invalid @enderror" 
                                id="target_user_id" name="target_user_id">
                            <option value="">Search and select user...</option>
                        </select>
                        @error('target_user_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i> Scheduling
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <div class="custom-control custom-radio">
                            <input class="custom-control-input" type="radio" id="send_now" 
                                   name="send_option" value="now" {{ old('send_option', 'now') == 'now' ? 'checked' : '' }}>
                            <label for="send_now" class="custom-control-label">
                                <i class="fas fa-bolt text-warning"></i> Send Immediately
                            </label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input class="custom-control-input" type="radio" id="send_scheduled" 
                                   name="send_option" value="scheduled" {{ old('send_option') == 'scheduled' ? 'checked' : '' }}>
                            <label for="send_scheduled" class="custom-control-label">
                                <i class="fas fa-calendar text-info"></i> Schedule for Later
                            </label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input class="custom-control-input" type="radio" id="send_draft" 
                                   name="send_option" value="draft" {{ old('send_option') == 'draft' ? 'checked' : '' }}>
                            <label for="send_draft" class="custom-control-label">
                                <i class="fas fa-save text-secondary"></i> Save as Draft
                            </label>
                        </div>
                    </div>
                    
                    <!-- Schedule Date/Time -->
                    <div class="form-group" id="scheduled-datetime" style="display: none;">
                        <label for="scheduled_at">Schedule Date & Time</label>
                        <input type="datetime-local" class="form-control @error('scheduled_at') is-invalid @enderror" 
                               id="scheduled_at" name="scheduled_at" value="{{ old('scheduled_at') }}"
                               min="{{ now()->format('Y-m-d\TH:i') }}">
                        @error('scheduled_at')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <!-- Recurring Options -->
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_recurring" 
                                   name="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_recurring">
                                <i class="fas fa-redo text-info"></i> Recurring Notification
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            Enable this for daily digest or regular announcements
                        </small>
                    </div>
                    
                    <!-- Recurring Frequency -->
                    <div class="form-group" id="recurring-options" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="recurring_frequency">Frequency</label>
                                <select class="form-control" id="recurring_frequency" name="recurring_frequency">
                                    <option value="daily" {{ old('recurring_frequency', 'daily') == 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ old('recurring_frequency') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ old('recurring_frequency') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="recurring_until">Recurring Until (Optional)</label>
                                <input type="date" class="form-control" id="recurring_until" 
                                       name="recurring_until" value="{{ old('recurring_until') }}"
                                       min="{{ now()->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Submit Buttons -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center">
                            <button type="submit" class="btn btn-success btn-lg" id="submit-btn">
                                <i class="fas fa-paper-plane"></i> <span id="submit-text">Send Now</span>
                            </button>
                            <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary btn-lg ml-2">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Preview Panel -->
    <div class="col-md-4">
        <div class="card card-outline card-info sticky-top">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-eye"></i> Live Preview
                </h3>
            </div>
            <div class="card-body">
                <div id="notification-preview" class="notification-preview">
                    <div class="alert alert-info">
                        <div class="d-flex align-items-start">
                            <i id="preview-icon" class="fas fa-info-circle mr-2 mt-1"></i>
                            <div class="flex-grow-1">
                                <h6 id="preview-title" class="alert-heading mb-1">
                                    Notification Title
                                </h6>
                                <p id="preview-message" class="mb-2">
                                    Your notification message will appear here as you type...
                                </p>
                                <div id="preview-action" style="display: none;">
                                    <a href="#" id="preview-action-link" class="btn btn-sm btn-outline-primary">
                                        Action Text
                                    </a>
                                </div>
                            </div>
                        </div>
                        <small id="preview-meta" class="text-muted d-block mt-2">
                            <i class="fas fa-clock"></i> Just now
                        </small>
                    </div>
                </div>
                
                <div class="mt-3">
                    <h6><i class="fas fa-users"></i> Target Audience</h6>
                    <p id="preview-target" class="text-muted">Select target audience</p>
                    
                    <h6 class="mt-3"><i class="fas fa-calendar"></i> Delivery</h6>
                    <p id="preview-delivery" class="text-muted">Send immediately</p>
                    
                    <div id="preview-recurring" style="display: none;" class="mt-3">
                        <h6><i class="fas fa-redo"></i> Recurring</h6>
                        <p id="preview-recurring-text" class="text-muted"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
    .required::after {
        content: " *";
        color: red;
    }
    
    .notification-preview {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 10px;
    }
    
    .sticky-top {
        top: 20px;
    }
    
    .alert-heading {
        font-size: 1rem;
    }
    
    #message-count {
        font-weight: bold;
    }
    
    .preview-badge {
        font-size: 0.7em;
    }
    
    .select2-container {
        width: 100% !important;
    }
    
    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(2.25rem + 2px);
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Message character counter
    $('#message').on('input', function() {
        const length = $(this).val().length;
        $('#message-count').text(length);
        
        if (length > 800) {
            $('#message-count').addClass('text-danger');
        } else if (length > 600) {
            $('#message-count').addClass('text-warning').removeClass('text-danger');
        } else {
            $('#message-count').removeClass('text-warning text-danger');
        }
    });
    
    // Show/hide target user selection
    $('#target_type').change(function() {
        const targetType = $(this).val();
        
        if (targetType === 'specific_user') {
            $('#target-user-group').show();
        } else {
            $('#target-user-group').hide();
            $('#target_user_id').val('');
        }
        
        updatePreview();
    });
    
    // Show/hide scheduled datetime
    $('input[name="send_option"]').change(function() {
        const sendOption = $(this).val();
        
        if (sendOption === 'scheduled') {
            $('#scheduled-datetime').show();
        } else {
            $('#scheduled-datetime').hide();
        }
        
        // Update submit button
        updateSubmitButton();
        updatePreview();
    });
    
    // Show/hide recurring options
    $('#is_recurring').change(function() {
        if ($(this).is(':checked')) {
            $('#recurring-options').show();
        } else {
            $('#recurring-options').hide();
        }
        updatePreview();
    });
    
    // Update submit button text
    function updateSubmitButton() {
        const sendOption = $('input[name="send_option"]:checked').val();
        let btnText = 'Send Now';
        let btnIcon = 'fas fa-paper-plane';
        
        switch(sendOption) {
            case 'scheduled':
                btnText = 'Schedule Notification';
                btnIcon = 'fas fa-clock';
                break;
            case 'draft':
                btnText = 'Save as Draft';
                btnIcon = 'fas fa-save';
                break;
        }
        
        $('#submit-text').text(btnText);
        $('#submit-btn i').attr('class', btnIcon);
    }
    
    // Live preview updates
    function updatePreview() {
        const title = $('#title').val() || 'Notification Title';
        const message = $('#message').val() || 'Your notification message will appear here as you type...';
        const type = $('#type').val() || 'info';
        const priority = $('#priority').val() || 'normal';
        const actionText = $('#action_text').val();
        const actionUrl = $('#action_url').val();
        const targetType = $('#target_type').val();
        const sendOption = $('input[name="send_option"]:checked').val();
        const isRecurring = $('#is_recurring').is(':checked');
        const recurringFreq = $('#recurring_frequency').val();
        const scheduledAt = $('#scheduled_at').val();
        
        // Update preview content
        $('#preview-title').text(title);
        $('#preview-message').text(message);
        
        // Update preview action
        if (actionText && actionUrl) {
            $('#preview-action').show();
            $('#preview-action-link').text(actionText).attr('href', actionUrl);
        } else {
            $('#preview-action').hide();
        }
        
        // Update preview styling based on type
        const alertClass = getAlertClass(type);
        const iconClass = getIconClass(type);
        
        $('#notification-preview .alert')
            .removeClass('alert-info alert-warning alert-danger alert-success')
            .addClass(alertClass);
        $('#preview-icon').attr('class', iconClass + ' mr-2 mt-1');
        
        // Add priority badge
        let priorityBadge = '';
        if (priority === 'urgent') {
            priorityBadge = '<span class="badge badge-danger preview-badge ml-1">URGENT</span>';
        } else if (priority === 'high') {
            priorityBadge = '<span class="badge badge-warning preview-badge ml-1">HIGH</span>';
        }
        
        $('#preview-title').html(title + priorityBadge);
        
        // Update target audience
        let targetText = 'Select target audience';
        switch(targetType) {
            case 'all_users':
                targetText = '👥 All Users';
                break;
            case 'specific_user':
                targetText = '👤 Specific User';
                break;
            case 'new_users':
                targetText = '🆕 New Users (Last 30 days)';
                break;
            case 'active_users':
                targetText = '⚡ Active Users (Last 7 days)';
                break;
        }
        $('#preview-target').text(targetText);
        
        // Update delivery info
        let deliveryText = 'Send immediately';
        switch(sendOption) {
            case 'scheduled':
                if (scheduledAt) {
                    const schedDate = new Date(scheduledAt);
                    deliveryText = '📅 Scheduled for ' + schedDate.toLocaleString();
                } else {
                    deliveryText = '📅 Schedule for later';
                }
                break;
            case 'draft':
                deliveryText = '💾 Save as draft';
                break;
        }
        $('#preview-delivery').text(deliveryText);
        
        // Update recurring info
        if (isRecurring) {
            $('#preview-recurring').show();
            $('#preview-recurring-text').text('🔄 Repeats ' + recurringFreq);
        } else {
            $('#preview-recurring').hide();
        }
    }
    
    function getAlertClass(type) {
        switch(type) {
            case 'warning': return 'alert-warning';
            case 'error': return 'alert-danger';
            case 'success': return 'alert-success';
            default: return 'alert-info';
        }
    }
    
    function getIconClass(type) {
        switch(type) {
            case 'warning': return 'fas fa-exclamation-triangle';
            case 'error': return 'fas fa-times-circle';
            case 'success': return 'fas fa-check-circle';
            case 'system_announcement': return 'fas fa-bullhorn';
            case 'daily_digest': return 'fas fa-chart-bar';
            default: return 'fas fa-info-circle';
        }
    }
    
    // Bind preview updates to all form changes
    $('#title, #message, #action_text, #action_url, #type, #priority, #target_type, #scheduled_at')
        .on('input change', updatePreview);
    
    $('#is_recurring, #recurring_frequency').on('change', updatePreview);
    
    // Initialize
    updateSubmitButton();
    updatePreview();
    $('#message').trigger('input'); // Initialize character counter
    
    // Initialize Select2 for user search
    $('#target_user_id').select2({
        theme: 'bootstrap4',
        placeholder: 'Search for a user...',
        allowClear: true,
        ajax: {
            url: '{{ route("admin.users.search") }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data.map(function(user) {
                        return {
                            id: user.id,
                            text: user.name + ' (' + user.email + ')'
                        };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 2
    });
});
</script>
@stop