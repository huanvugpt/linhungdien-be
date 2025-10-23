@extends('adminlte::page')

@section('title', 'Add New Category - Linhungdien.com')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Add New Category</h1>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary float-right">
                <i class="fas fa-arrow-left"></i> Back to Categories
            </a>
        </div>
    </div>
@stop

@section('content')
    {{-- Flash Messages --}}
    @include('admin.partials.flash-messages')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Category Information</h3>
        </div>
        
        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="name">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="slug">Slug (Optional)</label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                   id="slug" name="slug" value="{{ old('slug') }}" 
                                   placeholder="Leave empty to auto-generate from name">
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="color">Category Color</label>
                            <input type="color" class="form-control @error('color') is-invalid @enderror" 
                                   id="color" name="color" value="{{ old('color', '#007bff') }}">
                            @error('color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="sort_order">Sort Order</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                   id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="image">Category Image</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('image') is-invalid @enderror" 
                                           id="image" name="image" accept="image/*">
                                    <label class="custom-file-label" for="image">Choose file</label>
                                </div>
                            </div>
                            @error('image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Category
                </button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-default">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@stop

@section('js')
    <script>
        // Auto-generate slug from name
        document.getElementById('name').addEventListener('input', function() {
            const name = this.value;
            const slug = name.toLowerCase()
                            .replace(/[^a-z0-9]+/g, '-')
                            .replace(/(^-|-$)+/g, '');
            document.getElementById('slug').value = slug;
        });

        // Custom file input
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    </script>
@stop