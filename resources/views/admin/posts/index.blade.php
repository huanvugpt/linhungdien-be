@extends('adminlte::page')

@section('title', 'Posts Management - Linhungdien.com')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Posts Management</h1>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('admin.posts.pending') }}" class="btn btn-warning float-right mr-2">
                <i class="fas fa-clock"></i> Pending Posts
            </a>
            <a href="{{ route('admin.posts.create') }}" class="btn btn-primary float-right mr-2">
                <i class="fas fa-plus"></i> Add New Post
            </a>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> Success!</h5>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-ban"></i> Error!</h5>
            {{ session('error') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.posts.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <select name="category_id" class="form-control">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search posts..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-sync"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Posts List</h3>
        </div>
        
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Likes</th>
                        <th>Published At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                        <tr>
                            <td>{{ $post->id }}</td>
                            <td>
                                @if($post->is_featured)
                                    <i class="fas fa-star text-warning" title="Featured"></i>
                                @endif
                                <a href="{{ route('admin.posts.show', $post) }}" class="text-dark">
                                    {{ Str::limit($post->title, 50) }}
                                </a>
                            </td>
                            <td>
                                <span class="badge" style="background-color: {{ $post->category->color }}; color: white;">
                                    {{ $post->category->name }}
                                </span>
                            </td>
                            <td>{{ $post->author->name ?? 'N/A' }}</td>
                            <td>
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
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $post->views_count }}</span>
                            </td>
                            <td>
                                <span class="badge badge-danger">{{ $post->likes_count }}</span>
                            </td>
                            <td>
                                {{ $post->published_at ? $post->published_at->format('d/m/Y H:i') : 'N/A' }}
                            </td>
                            <td>
                                <a href="{{ route('admin.posts.show', $post) }}" class="btn btn-info btn-sm" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if($post->status === 'pending')
                                    <form action="{{ route('admin.posts.approve', $post) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.posts.reject', $post) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-sm" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('admin.posts.toggle-featured', $post) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-{{ $post->is_featured ? 'warning' : 'secondary' }} btn-sm" 
                                            title="{{ $post->is_featured ? 'Remove from Featured' : 'Mark as Featured' }}">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </form>
                                
                                <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-primary btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" 
                                      style="display: inline-block;" 
                                      onsubmit="return confirm('Are you sure you want to delete this post?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">No posts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($posts->hasPages())
            <div class="card-footer clearfix">
                {{ $posts->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
@stop

@section('js')
    <script>
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
@stop