@extends('adminlte::page')

@section('title', 'Edit Post - ' . $post->title)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Edit Post</h1>
        <div>
            <a href="{{ route('admin.posts.show', $post) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> View
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
            <div class="card-header">
                <h3 class="card-title">Post Details</h3>
            </div>
            <form action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data" id="post-form">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="title">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title', $post->title) }}" required>
                        @error('title')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                               id="slug" name="slug" value="{{ old('slug', $post->slug) }}">
                        <small class="form-text text-muted">URL-friendly version of the title</small>
                        @error('slug')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="excerpt">Excerpt</label>
                        <textarea class="form-control @error('excerpt') is-invalid @enderror" 
                                  id="excerpt" name="excerpt" rows="3">{{ old('excerpt', $post->excerpt) }}</textarea>
                        <small class="form-text text-muted">Short description of the post</small>
                        @error('excerpt')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="content">Content <span class="text-danger">*</span></label>
                        <div id="editor-container">
                            <div id="editor" style="min-height: 400px;"></div>
                        </div>
                        <textarea id="content" name="content" class="d-none @error('content') is-invalid @enderror" required>{{ old('content', $post->content) }}</textarea>
                        @error('content')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="featured_image">Featured Image</label>
                        @if($post->featured_image)
                            <div class="mb-2">
                                <img src="{{ Storage::url($post->featured_image) }}" 
                                     alt="Current featured image" 
                                     class="img-thumbnail" 
                                     style="max-height: 150px;">
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" id="remove_image" name="remove_image" value="1">
                                    <label class="form-check-label" for="remove_image">
                                        Remove current image
                                    </label>
                                </div>
                            </div>
                        @endif
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('featured_image') is-invalid @enderror" 
                                   id="featured_image" name="featured_image" accept="image/*">
                            <label class="custom-file-label" for="featured_image">
                                {{ $post->featured_image ? 'Replace image' : 'Choose file' }}
                            </label>
                        </div>
                        <small class="form-text text-muted">Recommended size: 1200x600 pixels</small>
                        @error('featured_image')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="tags">Tags</label>
                        <input type="text" class="form-control @error('tags') is-invalid @enderror" 
                               id="tags" name="tags" value="{{ old('tags', $post->tags) }}">
                        <small class="form-text text-muted">Separate multiple tags with commas</small>
                        @error('tags')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Publish Settings</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="category_id">Category <span class="text-danger">*</span></label>
                    <select class="form-control @error('category_id') is-invalid @enderror" 
                            id="category_id" name="category_id" required form="post-form">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" 
                                    {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="status">Status <span class="text-danger">*</span></label>
                    <select class="form-control @error('status') is-invalid @enderror" 
                            id="status" name="status" required form="post-form">
                        <option value="draft" {{ old('status', $post->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending" {{ old('status', $post->status) == 'pending' ? 'selected' : '' }}>Pending Review</option>
                        <option value="published" {{ old('status', $post->status) == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="rejected" {{ old('status', $post->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    @error('status')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1" 
                           {{ old('is_featured', $post->is_featured) ? 'checked' : '' }} form="post-form">
                    <label class="form-check-label" for="is_featured">
                        Featured Post
                    </label>
                </div>

                <div class="form-group">
                    <label for="published_at">Publish Date</label>
                    <input type="datetime-local" class="form-control @error('published_at') is-invalid @enderror" 
                           id="published_at" name="published_at" 
                           value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}" 
                           form="post-form">
                    <small class="form-text text-muted">Leave empty for immediate publishing</small>
                    @error('published_at')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary btn-block" form="post-form">
                    <i class="fas fa-save"></i> Update Post
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">SEO Settings</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="meta_title">Meta Title</label>
                    <input type="text" class="form-control @error('meta_title') is-invalid @enderror" 
                           id="meta_title" name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" 
                           maxlength="60" form="post-form">
                    <small class="form-text text-muted">Recommended: 50-60 characters</small>
                    @error('meta_title')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="meta_description">Meta Description</label>
                    <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                              id="meta_description" name="meta_description" rows="3" 
                              maxlength="160" form="post-form">{{ old('meta_description', $post->meta_description) }}</textarea>
                    <small class="form-text text-muted">Recommended: 150-160 characters</small>
                    @error('meta_description')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Post Statistics</h3>
            </div>
            <div class="card-body text-center">
                <div class="row">
                    <div class="col-6">
                        <h4 class="text-info">{{ number_format($post->views_count) }}</h4>
                        <small class="text-muted">Views</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-danger">{{ $post->likes_count }}</h4>
                        <small class="text-muted">Likes</small>
                    </div>
                </div>
                <hr>
                <small class="text-muted">
                    Created: {{ $post->created_at->format('d/m/Y H:i') }}<br>
                    Last updated: {{ $post->updated_at->format('d/m/Y H:i') }}
                </small>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <style>
        .custom-file-label::after {
            content: "Browse";
        }
        .form-group label {
            font-weight: 600;
        }
        .text-danger {
            color: #dc3545 !important;
        }
        .img-thumbnail {
            max-width: 200px;
        }
    </style>
@stop

@section('js')
<!-- CKEditor 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>

<script>
let editorInstance;

$(document).ready(function() {
    // Initialize CKEditor
    ClassicEditor
        .create(document.querySelector('#editor'), {
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'underline', 'strikethrough', '|',
                    'fontSize', 'fontColor', 'fontBackgroundColor', '|',
                    'alignment', '|',
                    'numberedList', 'bulletedList', '|',
                    'outdent', 'indent', '|',
                    'todoList', '|',
                    'link', 'insertImage', 'mediaEmbed', '|',
                    'insertTable', '|',
                    'blockQuote', 'codeBlock', '|',
                    'horizontalLine', '|',
                    'undo', 'redo'
                ]
            },
            language: 'en',
            image: {
                toolbar: [
                    'imageTextAlternative',
                    'imageStyle:inline',
                    'imageStyle:block',
                    'imageStyle:side',
                    '|',
                    'toggleImageCaption',
                    'imageResize'
                ]
            },
            table: {
                contentToolbar: [
                    'tableColumn',
                    'tableRow',
                    'mergeTableCells',
                    'tableCellProperties',
                    'tableProperties'
                ]
            }
        })
        .then(editor => {
            editorInstance = editor;
            
            // Set initial content
            const initialContent = $('#content').val();
            if (initialContent) {
                editor.setData(initialContent);
            }
            
            // Update hidden textarea when editor content changes
            editor.model.document.on('change:data', () => {
                $('#content').val(editor.getData());
            });
            
            // Set minimum height
            const editorElement = editor.ui.getEditableElement();
            editorElement.style.minHeight = '400px';
        })
        .catch(error => {
            console.error('Error initializing CKEditor:', error);
            // Fallback to regular textarea
            $('#editor-container').html('<textarea class="form-control" id="content-fallback" name="content" rows="15" required>' + $('#content').val() + '</textarea>');
            $('#content').remove();
            $('#content-fallback').attr('name', 'content');
        });

    // Auto-generate slug from title (only if slug is empty)
    $('#title').on('input', function() {
        let currentSlug = $('#slug').val();
        if (currentSlug === '' || currentSlug === '{{ $post->slug }}') {
            let slug = $(this).val()
                .toLowerCase()
                .replace(/[^\w ]+/g, '')
                .replace(/ +/g, '-');
            $('#slug').val(slug);
        }
    });

    // Custom file input
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName || 'Choose file');
    });

    // Character counters
    $('#meta_title').on('input', function() {
        let length = $(this).val().length;
        let color = length > 60 ? 'text-danger' : (length > 50 ? 'text-warning' : 'text-success');
        $(this).siblings('.form-text').removeClass('text-muted text-success text-warning text-danger').addClass(color);
    });

    $('#meta_description').on('input', function() {
        let length = $(this).val().length;
        let color = length > 160 ? 'text-danger' : (length > 150 ? 'text-warning' : 'text-success');
        $(this).siblings('.form-text').removeClass('text-muted text-success text-warning text-danger').addClass(color);
    });

    // Remove image checkbox
    $('#remove_image').on('change', function() {
        if ($(this).is(':checked')) {
            $('#featured_image').siblings('.custom-file-label').text('Choose new image');
        } else {
            $('#featured_image').siblings('.custom-file-label').text('Replace image');
        }
    });

    // Form submission - ensure editor data is synced
    $('#post-form').on('submit', function(e) {
        if (editorInstance) {
            $('#content').val(editorInstance.getData());
        }
    });
});
</script>
@stop