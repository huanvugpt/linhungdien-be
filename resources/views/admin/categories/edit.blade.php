@extends('adminlte::page')

@section('title', 'Edit Category - ' . $category->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Edit Category</h1>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Categories
        </a>
    </div>
@stop

@section('content')
{{-- Flash Messages --}}
@include('admin.partials.flash-messages')

<form id="category-form" action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Category Details</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $category->name) }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                               id="slug" name="slug" value="{{ old('slug', $category->slug) }}">
                        <small class="form-text text-muted">URL-friendly version of the name. Leave empty to auto-generate.</small>
                        @error('slug')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4">{{ old('description', $category->description) }}</textarea>
                        <small class="form-text text-muted">Brief description of what this category contains</small>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="color">Category Color</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror" 
                                   id="color" name="color" value="{{ old('color', $category->color ?? '#007bff') }}" 
                                   title="Choose category color">
                            <input type="text" class="form-control" id="color-text" 
                                   value="{{ old('color', $category->color ?? '#007bff') }}" readonly>
                        </div>
                        <small class="form-text text-muted">Color used for category badges and highlights</small>
                        @error('color')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Category Settings</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" 
                                   value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active Category</label>
                        </div>
                        <small class="form-text text-muted">Only active categories are visible to users</small>
                    </div>

                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                               id="sort_order" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" 
                               min="0">
                        <small class="form-text text-muted">Lower numbers appear first</small>
                        @error('sort_order')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i> Update Category
                    </button>
                </div>
            </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Category Statistics</h3>
            </div>
            <div class="card-body text-center">
                <div class="row">
                    <div class="col-12 mb-3">
                        <h3 class="text-primary">{{ $category->posts_count }}</h3>
                        <small class="text-muted">Total Posts</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <h4 class="text-success">{{ $category->posts()->where('status', 'published')->count() }}</h4>
                        <small class="text-muted">Published</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-warning">{{ $category->posts()->where('status', 'pending')->count() }}</h4>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Preview</h3>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="badge badge-lg" id="category-preview" 
                          style="background-color: {{ $category->color ?? '#007bff' }}; color: white;">
                        {{ $category->name }}
                    </span>
                </div>
                <small class="text-muted">This is how the category will appear on the site</small>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Information</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>Created:</th>
                        <td>{{ $category->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Updated:</th>
                        <td>{{ $category->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Current Slug:</th>
                        <td><code>{{ $category->slug }}</code></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</form>
@stop

@section('css')
<style>
    .form-control-color {
        width: 50px !important;
        padding: 0.375rem 0.25rem;
        height: calc(1.5em + 0.75rem + 2px);
    }
    
    .badge-lg {
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
    }
    
    .custom-control-label {
        font-weight: 500;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Auto-generate slug from name
    $('#name').on('input', function() {
        if ($('#slug').val() === '' || $('#slug').val() === '{{ $category->slug }}') {
            let slug = $(this).val()
                .toLowerCase()
                .replace(/[^\w ]+/g, '')
                .replace(/ +/g, '-');
            $('#slug').val(slug);
        }
    });

    // Update color preview
    $('#color').on('change', function() {
        let color = $(this).val();
        $('#color-text').val(color);
        $('#category-preview').css('background-color', color);
    });

    // Update preview name
    $('#name').on('input', function() {
        $('#category-preview').text($(this).val() || 'Category Name');
    });

    // Sync color text input
    $('#color-text').on('input', function() {
        let color = $(this).val();
        if (/^#[0-9A-F]{6}$/i.test(color)) {
            $('#color').val(color);
            $('#category-preview').css('background-color', color);
        }
    });
});
</script>
@stop