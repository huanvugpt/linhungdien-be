@extends('adminlte::page')

@section('title', 'Post Details - ' . $post->title)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Post Details</h1>
        <div>
            <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Posts
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">{{ $post->title }}</h3>
                <div>
                    @if($post->is_featured)
                        <span class="badge badge-warning"><i class="fas fa-star"></i> Featured</span>
                    @endif
                    @switch($post->status)
                        @case('draft')
                            <span class="badge badge-secondary">Draft</span>
                            @break
                        @case('pending')
                            <span class="badge badge-warning">Pending</span>
                            @break
                        @case('published')
                            <span class="badge badge-success">Published</span>
                            @break
                        @case('rejected')
                            <span class="badge badge-danger">Rejected</span>
                            @break
                    @endswitch
                </div>
            </div>
            <div class="card-body">
                @if($post->featured_image)
                    <div class="mb-3">
                        <img src="{{ Storage::url($post->featured_image) }}" 
                             alt="{{ $post->title }}" 
                             class="img-fluid rounded shadow-sm"
                             style="max-height: 300px; width: 100%; object-fit: cover;">
                    </div>
                @endif

                @if($post->excerpt)
                    <div class="alert alert-info">
                        <strong>Excerpt:</strong> {{ $post->excerpt }}
                    </div>
                @endif

                <div class="post-content">
                    {!! $post->content !!}
                </div>

                @if($post->tags)
                    <hr>
                    <div class="mb-3">
                        <strong>Tags:</strong>
                        @foreach(explode(',', $post->tags) as $tag)
                            <span class="badge badge-primary mr-1">{{ trim($tag) }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        @if($post->status === 'pending')
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title text-white">
                        <i class="fas fa-clock"></i> Approval Actions
                    </h3>
                </div>
                <div class="card-body">
                    <p class="mb-3">This post is pending approval. You can approve or reject it:</p>
                    <div class="btn-group">
                        <form action="{{ route('admin.posts.approve', $post) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success" 
                                    onclick="return confirm('Are you sure you want to approve this post?')">
                                <i class="fas fa-check"></i> Approve & Publish
                            </button>
                        </form>
                        <form action="{{ route('admin.posts.reject', $post) }}" method="POST" class="d-inline ml-2">
                            @csrf
                            <button type="submit" class="btn btn-danger" 
                                    onclick="return confirm('Are you sure you want to reject this post?')">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Post Information</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">ID:</th>
                        <td>{{ $post->id }}</td>
                    </tr>
                    <tr>
                        <th>Slug:</th>
                        <td><code>{{ $post->slug }}</code></td>
                    </tr>
                    <tr>
                        <th>Category:</th>
                        <td>
                            <span class="badge badge-info">{{ $post->category->name }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Author:</th>
                        <td>
                            @if($post->author)
                                {{ $post->author->name }}
                                <small class="text-muted">(User)</small>
                            @else
                                {{ $post->admin->name }}
                                <small class="text-muted">(Admin)</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Created:</th>
                        <td>{{ $post->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @if($post->published_at)
                    <tr>
                        <th>Published:</th>
                        <td>{{ $post->published_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endif
                    @if($post->approved_at && $post->approver)
                    <tr>
                        <th>Approved by:</th>
                        <td>
                            {{ $post->approver->name }}
                            <br><small class="text-muted">{{ $post->approved_at->format('d/m/Y H:i') }}</small>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Statistics</h3>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-right">
                            <h4 class="text-info">{{ number_format($post->views_count) }}</h4>
                            <small class="text-muted">Views</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-danger">{{ $post->likes_count }}</h4>
                        <small class="text-muted">Likes</small>
                    </div>
                </div>
            </div>
        </div>

        @if($post->meta_title || $post->meta_description)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">SEO Information</h3>
            </div>
            <div class="card-body">
                @if($post->meta_title)
                    <div class="mb-2">
                        <strong>Meta Title:</strong>
                        <p class="text-muted">{{ $post->meta_title }}</p>
                    </div>
                @endif
                @if($post->meta_description)
                    <div>
                        <strong>Meta Description:</strong>
                        <p class="text-muted">{{ $post->meta_description }}</p>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Actions</h3>
            </div>
            <div class="card-body">
                <div class="btn-group-vertical d-block">
                    <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-warning btn-block mb-2">
                        <i class="fas fa-edit"></i> Edit Post
                    </a>
                    
                    @if($post->status === 'published')
                        <a href="{{ route('posts.show', $post->slug) }}" target="_blank" class="btn btn-info btn-block mb-2">
                            <i class="fas fa-eye"></i> View on Site
                        </a>
                    @endif

                    <form action="{{ route('admin.posts.toggle-featured', $post) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-{{ $post->is_featured ? 'outline-warning' : 'warning' }} btn-block">
                            <i class="fas fa-star"></i> 
                            {{ $post->is_featured ? 'Remove Featured' : 'Mark as Featured' }}
                        </button>
                    </form>

                    <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this post? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Delete Post
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .post-content {
        font-size: 1.1em;
        line-height: 1.7;
        text-align: justify;
    }
    
    .post-content p {
        margin-bottom: 1.5em;
    }
    
    .card-header .badge {
        font-size: 0.8em;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
    }
    
    .border-right {
        border-right: 1px solid #dee2e6 !important;
    }
</style>
@stop