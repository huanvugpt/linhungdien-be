@extends('adminlte::page')

@section('title', 'Albums Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-images"></i> Albums Management
        </h1>
        <div>
            <a href="{{ route('admin.albums.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Album
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
                <h3>{{ $albums->total() }}</h3>
                <p>Total Albums</p>
            </div>
            <div class="icon">
                <i class="fas fa-images"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $albums->where('is_active', true)->count() }}</h3>
                <p>Active Albums</p>
            </div>
            <div class="icon">
                <i class="fas fa-eye"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $albums->sum('images_count') }}</h3>
                <p>Total Images</p>
            </div>
            <div class="icon">
                <i class="fas fa-photo-video"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ $albums->where('is_active', false)->count() }}</h3>
                <p>Inactive Albums</p>
            </div>
            <div class="icon">
                <i class="fas fa-eye-slash"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list"></i> All Albums
                </h3>
                <div class="card-tools">
                    <form method="GET" class="form-inline">
                        <div class="input-group input-group-sm mr-2">
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" placeholder="Search albums..." 
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
            <div class="card-body p-0">
                @if($albums->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="80">Cover</th>
                                    <th>Album Details</th>
                                    <th width="100">Images</th>
                                    <th width="80">Status</th>
                                    <th width="120">Created</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($albums as $album)
                                    <tr>
                                        <td>
                                            @if($album->cover_image)
                                                <img src="{{ Storage::url($album->cover_image) }}" 
                                                     alt="{{ $album->title }}" 
                                                     class="img-thumbnail" 
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                                     style="width: 60px; height: 60px; border-radius: 4px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <h6 class="mb-1">
                                                    <a href="{{ route('admin.albums.show', $album) }}" class="text-dark">
                                                        {{ $album->title }}
                                                    </a>
                                                </h6>
                                                @if($album->description)
                                                    <p class="text-muted mb-1 small">{{ Str::limit($album->description, 80) }}</p>
                                                @endif
                                                <small class="text-muted">
                                                    <i class="fas fa-link"></i> /albums/{{ $album->slug }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-info badge-lg">
                                                {{ $album->images_count }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($album->is_active)
                                                <span class="badge badge-success">
                                                    <i class="fas fa-eye"></i> Active
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">
                                                    <i class="fas fa-eye-slash"></i> Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                {{ $album->created_at->format('d/m/Y') }}
                                                <br><small class="text-muted">{{ $album->created_at->diffForHumans() }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.albums.show', $album) }}" 
                                                   class="btn btn-info" title="View Album">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.albums.edit', $album) }}" 
                                                   class="btn btn-warning" title="Edit Album">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.albums.destroy', $album) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" 
                                                            title="Delete Album"
                                                            onclick="return confirm('Delete this album and all its images? This action cannot be undone!')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-images fa-3x text-muted mb-3"></i>
                        <h4>No Albums Found</h4>
                        <p class="text-muted">Start organizing your images by creating your first album!</p>
                        <a href="{{ route('admin.albums.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Your First Album
                        </a>
                    </div>
                @endif
            </div>
            @if($albums->hasPages())
                <div class="card-footer">
                    {{ $albums->appends(request()->query())->links() }}
                </div>
            @endif
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
    
    .table td {
        vertical-align: middle;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .badge-lg {
        font-size: 1em;
        padding: 0.5em 0.8em;
    }
</style>
@stop