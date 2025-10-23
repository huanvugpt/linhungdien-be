@extends('adminlte::page')

@section('title', 'Category Details - ' . $category->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Category Details</h1>
        <div>
            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Categories
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">{{ $category->name }}</h3>
                <div>
                    <span class="badge badge-lg mr-2" style="background-color: {{ $category->color ?? '#007bff' }}; color: white;">
                        {{ $category->name }}
                    </span>
                    @if($category->is_active)
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-secondary">Inactive</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($category->description)
                    <div class="alert alert-info">
                        <strong>Description:</strong> {{ $category->description }}
                    </div>
                @endif

                <h5>Posts in this Category</h5>
                @if($category->posts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Status</th>
                                    <th>Views</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($category->posts()->latest()->take(10)->get() as $post)
                                    <tr>
                                        <td>
                                            @if($post->is_featured)
                                                <i class="fas fa-star text-warning" title="Featured"></i>
                                            @endif
                                            <a href="{{ route('admin.posts.show', $post) }}">
                                                {{ Str::limit($post->title, 40) }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($post->author)
                                                {{ $post->author->name }}
                                                <small class="text-muted">(User)</small>
                                            @else
                                                {{ $post->admin->name }}
                                                <small class="text-muted">(Admin)</small>
                                            @endif
                                        </td>
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
                                            <span class="badge badge-info">{{ number_format($post->views_count) }}</span>
                                        </td>
                                        <td>{{ $post->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.posts.show', $post) }}" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($category->posts->count() > 10)
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.posts.index', ['category' => $category->id]) }}" class="btn btn-outline-primary">
                                View All {{ $category->posts->count() }} Posts in this Category
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <h5>No Posts Yet</h5>
                        <p class="text-muted">This category doesn't have any posts yet.</p>
                        <a href="{{ route('admin.posts.create', ['category_id' => $category->id]) }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create First Post
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Category Information</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">ID:</th>
                        <td>{{ $category->id }}</td>
                    </tr>
                    <tr>
                        <th>Slug:</th>
                        <td><code>{{ $category->slug }}</code></td>
                    </tr>
                    <tr>
                        <th>Color:</th>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="badge mr-2" style="background-color: {{ $category->color ?? '#007bff' }}; width: 20px; height: 20px;"></div>
                                <code>{{ $category->color ?? '#007bff' }}</code>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Sort Order:</th>
                        <td>{{ $category->sort_order ?? 0 }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            @if($category->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Created:</th>
                        <td>{{ $category->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Updated:</th>
                        <td>{{ $category->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Statistics</h3>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-12 mb-3">
                        <h2 class="text-primary">{{ $category->posts->count() }}</h2>
                        <small class="text-muted">Total Posts</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-success">{{ $category->posts->where('status', 'published')->count() }}</h4>
                        <small class="text-muted">Published</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-warning">{{ $category->posts->where('status', 'pending')->count() }}</h4>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-secondary">{{ $category->posts->where('status', 'draft')->count() }}</h4>
                        <small class="text-muted">Draft</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-danger">{{ $category->posts->where('status', 'rejected')->count() }}</h4>
                        <small class="text-muted">Rejected</small>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <h4 class="text-info">{{ number_format($category->posts->sum('views_count')) }}</h4>
                    <small class="text-muted">Total Views</small>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="btn-group-vertical d-block">
                    <a href="{{ route('admin.posts.create', ['category_id' => $category->id]) }}" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-plus"></i> Add New Post
                    </a>
                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-warning btn-block mb-2">
                        <i class="fas fa-edit"></i> Edit Category
                    </a>
                    
                    @if($category->posts->count() > 0)
                        <a href="{{ route('admin.posts.index', ['category' => $category->id]) }}" class="btn btn-info btn-block mb-2">
                            <i class="fas fa-list"></i> View All Posts
                        </a>
                    @endif

                    <form action="{{ route('admin.categories.toggle', $category) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-{{ $category->is_active ? 'outline-secondary' : 'success' }} btn-block">
                            <i class="fas fa-{{ $category->is_active ? 'eye-slash' : 'eye' }}"></i> 
                            {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>

                    @if($category->posts->count() == 0)
                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this category? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-trash"></i> Delete Category
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .badge-lg {
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
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