@extends('adminlte::page')

@section('title', 'Edit Album: ' . $album->title)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-edit"></i> Edit Album: {{ $album->title }}
        </h1>
        <div>
            <a href="{{ route('admin.albums.show', $album) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> View Album
            </a>
            <a href="{{ route('admin.albums.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Albums
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <form action="{{ route('admin.albums.update', $album) }}" method="POST">
            @csrf
            @method('PUT')
            
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
                               id="title" name="title" value="{{ old('title', $album->title) }}" 
                               placeholder="Enter album title..." required>
                        @error('title')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">
                            Current slug: <code>/albums/{{ $album->slug }}</code>
                            <span id="slug-preview" class="text-info"></span>
                        </small>
                    </div>
                    
                    <!-- Description -->
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4" 
                                  placeholder="Describe this album...">{{ old('description', $album->description) }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <!-- Sort Order -->
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                               id="sort_order" name="sort_order" value="{{ old('sort_order', $album->sort_order) }}" 
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
                                   name="is_active" value="1" {{ old('is_active', $album->is_active) ? 'checked' : '' }}>
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
                        <i class="fas fa-save"></i> Update Album
                    </button>
                    <a href="{{ route('admin.albums.show', $album) }}" class="btn btn-info btn-lg ml-2">
                        <i class="fas fa-eye"></i> View Album
                    </a>
                    <a href="{{ route('admin.albums.index') }}" class="btn btn-secondary btn-lg ml-2">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Current Album Info -->
    <div class="col-md-4">
        <div class="card card-outline card-info sticky-top">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Current Album Info
                </h3>
            </div>
            <div class="card-body">
                <!-- Cover Image -->
                @if($album->cover_image)
                    <div class="text-center mb-3">
                        <img src="{{ Storage::url($album->cover_image) }}" 
                             class="img-thumbnail" 
                             style="max-width: 150px; max-height: 150px;"
                             alt="Album Cover">
                        <p class="small text-muted mt-1">Current Cover</p>
                    </div>
                @endif
                
                <table class="table table-sm table-borderless">
                    <tr>
                        <td><strong>Images:</strong></td>
                        <td>{{ $album->images->count() }} image(s)</td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            @if($album->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Created:</strong></td>
                        <td>{{ $album->created_at->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Updated:</strong></td>
                        <td>{{ $album->updated_at->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Creator:</strong></td>
                        <td>{{ $album->creator->name }}</td>
                    </tr>
                </table>
                
                <hr>
                
                <h6><i class="fas fa-chart-bar text-info"></i> Quick Stats</h6>
                <div class="row text-center">
                    <div class="col-4">
                        <div class="info-box bg-info">
                            <div class="info-box-content">
                                <span class="info-box-number">{{ $album->images->count() }}</span>
                                <span class="info-box-text">Images</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="info-box bg-success">
                            <div class="info-box-content">
                                <span class="info-box-number">{{ $album->images->where('is_featured', true)->count() }}</span>
                                <span class="info-box-text">Featured</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="info-box bg-warning">
                            <div class="info-box-content">
                                <span class="info-box-number">{{ $album->sort_order }}</span>
                                <span class="info-box-text">Sort Order</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="text-center">
                    <a href="{{ $album->url }}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-external-link-alt"></i> View Public Album
                    </a>
                </div>
                
                <hr>
                
                <!-- Danger Zone -->
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle"></i> Danger Zone</h6>
                    <p class="small mb-2">Permanently delete this album and all its images.</p>
                    <form action="{{ route('admin.albums.destroy', $album) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" 
                                onclick="return confirm('Are you sure? This will delete the album and ALL its images permanently. This action cannot be undone!')">
                            <i class="fas fa-trash"></i> Delete Album
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
    
    .info-box {
        border-radius: 8px;
        margin-bottom: 10px;
    }
    
    .info-box-content {
        padding: 10px;
    }
    
    .info-box-number {
        font-size: 1.2rem;
        font-weight: bold;
        display: block;
    }
    
    .info-box-text {
        font-size: 0.8rem;
        text-transform: uppercase;
    }
    
    #slug-preview {
        font-weight: bold;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    const originalTitle = '{{ $album->title }}';
    const originalSlug = '{{ $album->slug }}';
    
    // Auto-generate slug preview when title changes
    $('#title').on('input', function() {
        const title = $(this).val();
        
        if (title !== originalTitle) {
            const slug = title.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
                .replace(/\s+/g, '-') // Replace spaces with hyphens
                .replace(/-+/g, '-') // Replace multiple hyphens with single
                .trim('-'); // Remove leading/trailing hyphens
                
            if (slug !== originalSlug) {
                $('#slug-preview').html('<br>New slug will be: <code>/albums/' + slug + '</code>');
            } else {
                $('#slug-preview').html('');
            }
        } else {
            $('#slug-preview').html('');
        }
    });
});
</script>
@stop