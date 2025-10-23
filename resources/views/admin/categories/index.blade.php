@extends('adminlte::page')

@section('title', 'Categories - Linhungdien.com')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Categories Management</h1>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary float-right">
                <i class="fas fa-plus"></i> Add New Category
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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Categories List</h3>
            <div class="card-tools">
                <form action="{{ route('admin.categories.index') }}" method="GET">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" name="search" class="form-control float-right" 
                               placeholder="Search categories..." value="{{ request('search') }}">
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
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Color</th>
                        <th>Posts Count</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>
                                <span class="badge" style="background-color: {{ $category->color }}; color: white;">
                                    {{ $category->name }}
                                </span>
                            </td>
                            <td>
                                <span class="color-preview" style="background-color: {{ $category->color }}; width: 20px; height: 20px; display: inline-block; border-radius: 50%; border: 1px solid #ddd;"></span>
                                {{ $category->color }}
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $category->posts_count ?? 0 }}</span>
                            </td>
                            <td>
                                @if($category->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $category->sort_order }}</td>
                            <td>{{ $category->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <form action="{{ route('admin.categories.toggle', $category) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-{{ $category->is_active ? 'warning' : 'success' }} btn-sm" 
                                            title="{{ $category->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-{{ $category->is_active ? 'eye-slash' : 'eye' }}"></i>
                                    </button>
                                </form>
                                
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" 
                                      style="display: inline-block;" 
                                      onsubmit="return confirm('Are you sure you want to delete this category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($categories->hasPages())
            <div class="card-footer clearfix">
                {{ $categories->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
@stop

@section('css')
    <style>
        .color-preview {
            border: 1px solid #ddd;
        }
    </style>
@stop

@section('js')
    <script>
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
@stop