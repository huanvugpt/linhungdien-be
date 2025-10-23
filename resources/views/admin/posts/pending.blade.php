@extends('adminlte::page')

@section('title', 'Pending Posts')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            Pending Posts 
            <small class="badge badge-warning">{{ $posts->total() }}</small>
        </h1>
        <div>
            <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Post
            </a>
            <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> All Posts
            </a>
        </div>
    </div>
@stop

@section('content')
@if($posts->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Posts Waiting for Approval</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Post</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Submitted</th>
                        <th width="200">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($posts as $post)
                        <tr>
                            <td>
                                <div class="d-flex align-items-start">
                                    @if($post->featured_image)
                                        <img src="{{ Storage::url($post->featured_image) }}" 
                                             alt="{{ $post->title }}" 
                                             class="img-thumbnail mr-3" 
                                             style="width: 60px; height: 60px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded mr-3 d-flex align-items-center justify-content-center" 
                                             style="width: 60px; height: 60px;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <h6 class="mb-1">
                                            <a href="{{ route('admin.posts.show', $post) }}" class="text-dark">
                                                {{ Str::limit($post->title, 50) }}
                                            </a>
                                            @if($post->is_featured)
                                                <i class="fas fa-star text-warning ml-1" title="Featured"></i>
                                            @endif
                                        </h6>
                                        @if($post->excerpt)
                                            <p class="text-muted mb-0 small">{{ Str::limit($post->excerpt, 80) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($post->author)
                                    <div>
                                        <strong>{{ $post->author->name }}</strong>
                                        <br><small class="text-muted">User</small>
                                    </div>
                                @else
                                    <div>
                                        <strong>{{ $post->admin->name }}</strong>
                                        <br><small class="text-muted">Admin</small>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $post->category->name }}</span>
                            </td>
                            <td>
                                <div>
                                    {{ $post->created_at->format('d/m/Y') }}
                                    <br><small class="text-muted">{{ $post->created_at->format('H:i') }}</small>
                                </div>
                                <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                <div class="btn-group-vertical btn-group-sm">
                                    <a href="{{ route('admin.posts.show', $post) }}" class="btn btn-info btn-sm mb-1">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <div class="btn-group btn-group-sm">
                                        <form action="{{ route('admin.posts.approve', $post) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm" 
                                                    onclick="return confirm('Approve and publish this post?')"
                                                    title="Approve & Publish">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.posts.reject', $post) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm ml-1" 
                                                    onclick="return confirm('Reject this post?')"
                                                    title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($posts->hasPages())
            <div class="card-footer">
                {{ $posts->links() }}
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title text-white">
                        <i class="fas fa-info-circle"></i> Bulk Actions
                    </h3>
                </div>
                <div class="card-body">
                    <form id="bulk-action-form" method="POST">
                        @csrf
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <select name="bulk_action" class="form-control" required>
                                    <option value="">Choose Action</option>
                                    <option value="approve">Approve Selected</option>
                                    <option value="reject">Reject Selected</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="select-all">
                                    <label class="form-check-label" for="select-all">
                                        Select all posts on this page
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-warning btn-block" 
                                        onclick="return confirm('Are you sure you want to perform this bulk action?')">
                                    <i class="fas fa-bolt"></i> Execute
                                </button>
                            </div>
                        </div>
                        
                        <!-- Hidden checkboxes for bulk selection -->
                        <div class="mt-3" id="bulk-checkboxes" style="display: none;">
                            @foreach($posts as $post)
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" class="form-check-input post-checkbox" 
                                           name="post_ids[]" value="{{ $post->id }}" id="post-{{ $post->id }}">
                                    <label class="form-check-label" for="post-{{ $post->id }}">
                                        {{ Str::limit($post->title, 30) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <h4>No Pending Posts</h4>
            <p class="text-muted">All posts have been reviewed. Great job!</p>
            <a href="{{ route('admin.posts.index') }}" class="btn btn-primary">
                <i class="fas fa-list"></i> View All Posts
            </a>
        </div>
    </div>
@endif
@stop

@section('css')
<style>
    .img-thumbnail {
        border: 1px solid #dee2e6;
    }
    
    .btn-group-vertical .btn {
        border-radius: 0.25rem;
    }
    
    .btn-group-vertical .btn + .btn {
        margin-left: 0;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .badge {
        font-size: 0.8em;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Bulk selection functionality
    $('#select-all').on('change', function() {
        let isChecked = $(this).is(':checked');
        $('.post-checkbox').prop('checked', isChecked);
        toggleBulkCheckboxes(isChecked);
    });
    
    function toggleBulkCheckboxes(show) {
        if (show) {
            $('#bulk-checkboxes').slideDown();
        } else {
            $('#bulk-checkboxes').slideUp();
        }
    }
    
    // Handle bulk action form submission
    $('#bulk-action-form').on('submit', function(e) {
        let selectedPosts = $('.post-checkbox:checked').length;
        let action = $('select[name="bulk_action"]').val();
        
        if (selectedPosts === 0) {
            e.preventDefault();
            alert('Please select at least one post.');
            return false;
        }
        
        if (!action) {
            e.preventDefault();
            alert('Please select an action.');
            return false;
        }
        
        // Set form action based on bulk action
        let formAction = action === 'approve' 
            ? '{{ route("admin.posts.bulk-approve") }}' 
            : '{{ route("admin.posts.bulk-reject") }}';
        $(this).attr('action', formAction);
    });
    
    // Individual action confirmations
    $('.btn-success').on('click', function(e) {
        let postTitle = $(this).closest('tr').find('h6 a').text();
        return confirm('Are you sure you want to approve and publish "' + postTitle.trim() + '"?');
    });
    
    $('.btn-danger').on('click', function(e) {
        let postTitle = $(this).closest('tr').find('h6 a').text();
        return confirm('Are you sure you want to reject "' + postTitle.trim() + '"?');
    });
});
</script>
@stop