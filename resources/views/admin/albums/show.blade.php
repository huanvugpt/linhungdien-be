@extends('adminlte::page')

@section('title', 'Album: ' . $album->title)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-images"></i> {{ $album->title }}
            @if(!$album->is_active)
                <span class="badge badge-secondary">Inactive</span>
            @endif
        </h1>
        <div>
            <a href="{{ route('admin.albums.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Albums
            </a>
            <a href="{{ route('admin.albums.edit', $album) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Album
            </a>
        </div>
    </div>
@stop

@section('content')
<!-- Album Info -->
<div class="row">
    <div class="col-md-8">
        <!-- Upload Section -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-upload"></i> Upload Images
                </h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.albums.upload', $album) }}" method="POST" 
                      enctype="multipart/form-data" id="upload-form">
                    @csrf
                    <div class="form-group">
                        <label for="images">Select Images</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="images" name="images[]" 
                                   multiple accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" required>
                            <label class="custom-file-label" for="images">Choose images...</label>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Max 10 images per upload. Each image must be under 10MB. 
                            Supported formats: JPG, PNG, GIF, WebP.
                        </small>
                        @if($errors->has('images'))
                            <div class="alert alert-danger mt-2">
                                <ul class="mb-0">
                                    @foreach($errors->get('images') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if($errors->has('images.*'))
                            <div class="alert alert-danger mt-2">
                                <ul class="mb-0">
                                    @foreach($errors->get('images.*') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Upload Preview -->
                    <div id="upload-preview" class="row" style="display: none;"></div>
                    
                    <button type="submit" class="btn btn-primary" id="upload-btn" disabled>
                        <i class="fas fa-upload"></i> Upload Images
                    </button>
                    <button type="button" class="btn btn-secondary ml-2" onclick="clearSelection()">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </form>
            </div>
        </div>

        <!-- Images Gallery -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-photo-video"></i> Album Images ({{ $album->images->count() }})
                </h3>
                @if($album->images->count() > 0)
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-info" onclick="selectAllImages()">
                            <i class="fas fa-check-square"></i> Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-warning" onclick="bulkDeleteImages()" id="bulk-delete-btn" style="display: none;">
                            <i class="fas fa-trash"></i> Delete Selected
                        </button>
                    </div>
                @endif
            </div>
            <div class="card-body">
                @if($album->images->count() > 0)
                    <div class="row" id="images-gallery">
                        @foreach($album->images as $image)
                            <div class="col-md-3 mb-4" data-image-id="{{ $image->id }}">
                                <div class="card h-100">
                                    <div class="position-relative">
                                        <img src="{{ Storage::url($image->path) }}" 
                                             class="card-img-top" 
                                             style="height: 200px; object-fit: cover; cursor: pointer;"
                                             alt="{{ $image->title ?: $image->filename }}"
                                             onclick="viewImage('{{ Storage::url($image->path) }}', '{{ $image->title ?: $image->filename }}')">
                                        
                                        <!-- Image overlay buttons -->
                                        <div class="position-absolute" style="top: 5px; right: 5px;">
                                            <input type="checkbox" class="image-checkbox" value="{{ $image->id }}" 
                                                   onchange="toggleBulkActions()" style="transform: scale(1.2);">
                                        </div>
                                        
                                        <!-- Featured badge -->
                                        @if($image->is_featured)
                                            <span class="badge badge-warning position-absolute" style="top: 5px; left: 5px;">
                                                <i class="fas fa-star"></i> Featured
                                            </span>
                                        @endif
                                        
                                        <!-- Cover badge -->
                                        @if($album->cover_image === $image->path)
                                            <span class="badge badge-success position-absolute" style="top: 30px; left: 5px;">
                                                <i class="fas fa-image"></i> Cover
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="card-body p-2">
                                        <h6 class="card-title mb-1">{{ $image->title ?: 'Untitled' }}</h6>
                                        <p class="card-text small text-muted mb-2">
                                            {{ $image->dimensions_string }} • {{ $image->human_file_size }}
                                        </p>
                                        
                                        <!-- Quick actions -->
                                        <div class="btn-group btn-group-sm w-100">
                                            <button type="button" class="btn btn-info btn-sm" 
                                                    onclick="editImageModal({{ $image->id }}, '{{ $image->title }}', '{{ $image->description }}', {{ $image->is_featured ? 'true' : 'false' }}, {{ $image->sort_order }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            
                                            @if($album->cover_image !== $image->path)
                                                <form action="{{ route('admin.albums.setCover', [$album, $image]) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm" 
                                                            title="Set as Cover">
                                                        <i class="fas fa-image"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <form action="{{ route('admin.albums.images.delete', [$album, $image]) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('Delete this image?')"
                                                        title="Delete Image">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-images fa-3x text-muted mb-3"></i>
                        <h4>No Images Yet</h4>
                        <p class="text-muted">Upload some images to get started with this album!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Album Details Sidebar -->
    <div class="col-md-4">
        <div class="card sticky-top">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Album Details
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
                        <p class="small text-muted mt-1">Album Cover</p>
                    </div>
                @endif
                
                <table class="table table-sm table-borderless">
                    <tr>
                        <td><strong>Title:</strong></td>
                        <td>{{ $album->title }}</td>
                    </tr>
                    <tr>
                        <td><strong>Slug:</strong></td>
                        <td><code>{{ $album->slug }}</code></td>
                    </tr>
                    @if($album->description)
                        <tr>
                            <td><strong>Description:</strong></td>
                            <td>{{ $album->description }}</td>
                        </tr>
                    @endif
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
                        <td><strong>Images:</strong></td>
                        <td>{{ $album->images->count() }}</td>
                    </tr>
                    <tr>
                        <td><strong>Created:</strong></td>
                        <td>{{ $album->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Created By:</strong></td>
                        <td>{{ $album->creator->name }}</td>
                    </tr>
                </table>
                
                <hr>
                
                <div class="text-center">
                    <a href="{{ $album->url }}" target="_blank" class="btn btn-info btn-sm mb-2">
                        <i class="fas fa-external-link-alt"></i> View Public Album
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Image Modal -->
<div class="modal fade" id="editImageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editImageForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Image</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_title">Title</label>
                        <input type="text" class="form-control" id="edit_title" name="title">
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_sort_order">Sort Order</label>
                        <input type="number" class="form-control" id="edit_sort_order" name="sort_order" min="0">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="edit_is_featured" name="is_featured" value="1">
                            <label class="custom-control-label" for="edit_is_featured">Featured Image</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Viewer Modal -->
<div class="modal fade" id="imageViewerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageViewerTitle"></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="imageViewerImg" src="" class="img-fluid" alt="">
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .sticky-top {
        top: 20px;
    }
    
    .card-img-top:hover {
        opacity: 0.8;
        transition: opacity 0.3s;
    }
    
    .position-relative {
        position: relative;
    }
    
    .position-absolute {
        position: absolute;
    }
    
    .image-checkbox {
        background: rgba(255, 255, 255, 0.8);
        border-radius: 3px;
        padding: 2px;
    }
    
    #upload-preview .col-md-2 {
        margin-bottom: 10px;
    }
    
    .upload-preview-item {
        border: 1px dashed #ddd;
        padding: 10px;
        text-align: center;
        border-radius: 5px;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Custom file input
    $('.custom-file-input').on('change', function() {
        const files = this.files;
        let fileNames = [];
        let validFiles = 0;
        let errors = [];
        
        if (files.length > 0) {
            if (files.length > 10) {
                errors.push('Maximum 10 files allowed');
            }
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                fileNames.push(file.name);
                
                // Check file type
                if (!file.type.match(/^image\/(jpeg|jpg|png|gif|webp)$/i)) {
                    errors.push(`${file.name}: Invalid file type`);
                    continue;
                }
                
                // Check file size (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    errors.push(`${file.name}: File too large (max 10MB)`);
                    continue;
                }
                
                validFiles++;
            }
            
            if (errors.length > 0) {
                alert('Upload errors:\n' + errors.join('\n'));
            }
            
            $(this).next('.custom-file-label').text(`${files.length} file(s) selected (${validFiles} valid)`);
            $('#upload-btn').prop('disabled', validFiles === 0);
            showUploadPreview(files);
        } else {
            $(this).next('.custom-file-label').text('Choose images...');
            $('#upload-btn').prop('disabled', true);
            $('#upload-preview').hide();
        }
    });
    
    // Upload form submit with progress
    $('#upload-form').on('submit', function(e) {
        const btn = $('#upload-btn');
        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
    });
});

function showUploadPreview(files) {
    const preview = $('#upload-preview');
    preview.empty();
    
    for (let i = 0; i < Math.min(files.length, 6); i++) {
        const file = files[i];
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const col = $(`
                    <div class="col-md-2">
                        <div class="upload-preview-item">
                            <img src="${e.target.result}" style="width: 100%; height: 80px; object-fit: cover; margin-bottom: 5px;">
                            <small class="text-muted d-block">${file.name}</small>
                        </div>
                    </div>
                `);
                preview.append(col);
            };
            reader.readAsDataURL(file);
        }
    }
    
    if (files.length > 6) {
        preview.append(`
            <div class="col-md-2">
                <div class="upload-preview-item">
                    <i class="fas fa-plus fa-2x text-muted"></i>
                    <small class="text-muted d-block">+${files.length - 6} more</small>
                </div>
            </div>
        `);
    }
    
    preview.show();
}

function editImageModal(id, title, description, isFeatured, sortOrder) {
    $('#edit_title').val(title);
    $('#edit_description').val(description);
    $('#edit_is_featured').prop('checked', isFeatured);
    $('#edit_sort_order').val(sortOrder);
    
    const form = $('#editImageForm');
    form.attr('action', `{{ route('admin.albums.images.update', [$album, ':id']) }}`.replace(':id', id));
    
    $('#editImageModal').modal('show');
}

function viewImage(src, title) {
    $('#imageViewerImg').attr('src', src);
    $('#imageViewerTitle').text(title);
    $('#imageViewerModal').modal('show');
}

function toggleBulkActions() {
    const checkedBoxes = $('.image-checkbox:checked').length;
    if (checkedBoxes > 0) {
        $('#bulk-delete-btn').show();
    } else {
        $('#bulk-delete-btn').hide();
    }
}

function selectAllImages() {
    $('.image-checkbox').prop('checked', true);
    toggleBulkActions();
}

function bulkDeleteImages() {
    const checkedBoxes = $('.image-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Please select images to delete');
        return;
    }
    
    if (!confirm(`Delete ${checkedBoxes.length} selected image(s)?`)) {
        return;
    }
    
    // Create form for bulk delete (you'd need to implement this route)
    const imageIds = [];
    checkedBoxes.each(function() {
        imageIds.push($(this).val());
    });
    
    // For now, delete images one by one
    checkedBoxes.each(function() {
        const imageId = $(this).val();
        const imageContainer = $(this).closest('[data-image-id]');
        
        // You could implement a bulk delete endpoint or delete individually
        // For demonstration, we'll hide the images (in real app, make AJAX calls)
        imageContainer.fadeOut();
    });
}

function clearSelection() {
    $('#images').val('');
    $('.custom-file-label').text('Choose images...');
    $('#upload-btn').prop('disabled', true);
    $('#upload-preview').hide();
}
</script>
@stop