@extends('adminlte::page')

@section('title', 'Create New Album')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-plus"></i> Create New Album
        </h1>
        <div>
            <a href="{{ route('admin.albums.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Albums
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <form action="{{ route('admin.albums.store') }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Album Information
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Title -->
                    <div class="form-group">
                        <label for="title" class="required">Album Title</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title') }}" 
                               placeholder="Enter album title..." required>
                        @error('title')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">
                            This will be used to generate the album URL slug
                        </small>
                    </div>
                    
                    <!-- Description -->
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4" 
                                  placeholder="Describe this album...">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <!-- Sort Order -->
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                               id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" 
                               min="0" placeholder="0">
                        @error('sort_order')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">
                            Lower numbers appear first. Use 0 for default ordering.
                        </small>
                    </div>
                    
                    <!-- Status -->
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" 
                                   name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">
                                <i class="fas fa-eye text-success"></i> Active Album
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            Inactive albums won't be visible to public users
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="card">
                <div class="card-body text-center">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save"></i> Create Album
                    </button>
                    <a href="{{ route('admin.albums.index') }}" class="btn btn-secondary btn-lg ml-2">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Help Panel -->
    <div class="col-md-4">
        <div class="card card-outline card-info sticky-top">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-question-circle"></i> Album Guidelines
                </h3>
            </div>
            <div class="card-body">
                <h6><i class="fas fa-info-circle text-info"></i> Album Creation Tips</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Choose descriptive titles</li>
                    <li><i class="fas fa-check text-success"></i> Add meaningful descriptions</li>
                    <li><i class="fas fa-check text-success"></i> Use sort order for organization</li>
                </ul>
                
                <hr>
                
                <h6><i class="fas fa-upload text-primary"></i> Image Upload Rules</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Max file size: <strong>2MB</strong></li>
                    <li><i class="fas fa-check text-success"></i> Formats: JPG, PNG, GIF</li>
                    <li><i class="fas fa-check text-success"></i> Multiple files supported</li>
                    <li><i class="fas fa-check text-success"></i> Auto-resize available</li>
                </ul>
                
                <hr>
                
                <h6><i class="fas fa-cog text-warning"></i> Next Steps</h6>
                <p class="text-muted small">
                    After creating the album, you'll be able to:
                </p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-arrow-right text-muted"></i> Upload images</li>
                    <li><i class="fas fa-arrow-right text-muted"></i> Set cover image</li>
                    <li><i class="fas fa-arrow-right text-muted"></i> Organize image order</li>
                    <li><i class="fas fa-arrow-right text-muted"></i> Edit image details</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .required::after {
        content: " *";
        color: red;
    }
    
    .sticky-top {
        top: 20px;
    }
    
    .card-outline.card-info {
        border-color: #17a2b8;
    }
    
    .list-unstyled li {
        margin-bottom: 0.5rem;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Auto-generate slug preview
    $('#title').on('input', function() {
        const title = $(this).val();
        const slug = title.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
            .replace(/\s+/g, '-') // Replace spaces with hyphens
            .replace(/-+/g, '-') // Replace multiple hyphens with single
            .trim('-'); // Remove leading/trailing hyphens
            
        // Show preview (you can add a preview element if needed)
        console.log('Album URL will be: /albums/' + slug);
    });
});
</script>
@stop