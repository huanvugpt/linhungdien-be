@extends('adminlte::page')

@section('title', 'Edit Video: ' . $video->title . ' - Linhungdien.com')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Edit Video</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.videos.index') }}">Videos</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </div>
</div>
@stop

@section('content')
        <div class="row">
            <div class="col-12">
                <!-- Alert Messages -->
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-ban"></i> Lỗi!</h5>
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-check"></i> Thành công!</h5>
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-ban"></i> Có lỗi xảy ra!</h5>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Thông tin Video</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.videos.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                            <a href="{{ $video->youtube_url }}" target="_blank" class="btn btn-info">
                                <i class="fas fa-external-link-alt"></i> Xem video
                            </a>
                        </div>
                    </div>

                    <form action="{{ route('admin.videos.update', $video) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- Title -->
                                    <div class="form-group">
                                        <label for="title">Tiêu đề <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                               id="title" name="title" value="{{ old('title', $video->title) }}" required>
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Excerpt -->
                                    <div class="form-group">
                                        <label for="excerpt">Tóm tắt</label>
                                        <textarea class="form-control @error('excerpt') is-invalid @enderror" 
                                                  id="excerpt" name="excerpt" rows="3" 
                                                  placeholder="Mô tả ngắn gọn về video...">{{ old('excerpt', $video->excerpt) }}</textarea>
                                        @error('excerpt')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Tối đa 500 ký tự</small>
                                    </div>

                                    <!-- Content -->
                                    <div class="form-group">
                                        <label for="content">Nội dung chi tiết</label>
                                        <textarea class="form-control @error('content') is-invalid @enderror" 
                                                  id="content" name="content" rows="10" 
                                                  placeholder="Nội dung chi tiết về video...">{{ old('content', $video->content) }}</textarea>
                                        @error('content')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Hỗ trợ HTML và Markdown</small>
                                    </div>

                                    <!-- Description -->
                                    <div class="form-group">
                                        <label for="description">Mô tả (SEO)</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  id="description" name="description" rows="3" 
                                                  placeholder="Mô tả cho SEO...">{{ old('description', $video->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Tối đa 1000 ký tự</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <!-- Current Video Preview -->
                                    <div class="form-group">
                                        <label>Video hiện tại</label>
                                        <div class="embed-responsive embed-responsive-16by9 mb-3">
                                            <iframe class="embed-responsive-item" 
                                                    src="https://www.youtube.com/embed/{{ $video->youtube_id }}" 
                                                    frameborder="0" allowfullscreen></iframe>
                                        </div>
                                    </div>

                                    <!-- YouTube URL -->
                                    <div class="form-group">
                                        <label for="youtube_url">Link YouTube <span class="text-danger">*</span></label>
                                        <input type="url" class="form-control @error('youtube_url') is-invalid @enderror" 
                                               id="youtube_url" name="youtube_url" value="{{ old('youtube_url', $video->youtube_url) }}" 
                                               placeholder="https://www.youtube.com/watch?v=..." required>
                                        @error('youtube_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Hỗ trợ các định dạng:<br>
                                            - youtube.com/watch?v=...<br>
                                            - youtu.be/...<br>
                                            - youtube.com/embed/...
                                        </small>
                                    </div>

                                    <!-- Category -->
                                    <div class="form-group">
                                        <label for="category_id">Danh mục <span class="text-danger">*</span></label>
                                        <select class="form-control @error('category_id') is-invalid @enderror" 
                                                id="category_id" name="category_id" required>
                                            <option value="">Chọn danh mục</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" 
                                                        {{ old('category_id', $video->category_id) == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Status Options -->
                                    <div class="form-group">
                                        <label>Trạng thái</label>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" 
                                                   id="is_active" name="is_active" value="1" 
                                                   {{ old('is_active', $video->is_active) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="is_active">Hoạt động</label>
                                        </div>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" 
                                                   id="is_featured" name="is_featured" value="1"
                                                   {{ old('is_featured', $video->is_featured) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="is_featured">Video nổi bật</label>
                                        </div>
                                    </div>

                                    <!-- Video Stats -->
                                    <div class="form-group">
                                        <label>Thống kê</label>
                                        <div class="info-box">
                                            <span class="info-box-icon bg-info"><i class="fas fa-eye"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Lượt xem</span>
                                                <span class="info-box-number">{{ number_format($video->views_count) }}</span>
                                            </div>
                                        </div>
                                        <div class="info-box">
                                            <span class="info-box-icon bg-success"><i class="fas fa-heart"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Lượt thích</span>
                                                <span class="info-box-number">{{ number_format($video->likes_count) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- New Preview -->
                                    <div class="form-group">
                                        <label>Xem trước (nếu thay đổi link)</label>
                                        <div id="video-preview" class="text-center" style="display: none;">
                                            <div class="embed-responsive embed-responsive-16by9">
                                                <iframe id="preview-iframe" class="embed-responsive-item" 
                                                        src="" frameborder="0" allowfullscreen></iframe>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cập nhật Video
                            </button>
                            <a href="{{ route('admin.videos.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                            <div class="float-right">
                                <small class="text-muted">
                                    Tạo: {{ $video->created_at->format('d/m/Y H:i') }} | 
                                    Cập nhật: {{ $video->updated_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    var originalUrl = $('#youtube_url').val();

    // YouTube URL Preview
    $('#youtube_url').on('blur', function() {
        var url = $(this).val();
        if (url && url !== originalUrl) {
            var videoId = extractYouTubeID(url);
            if (videoId) {
                $('#preview-iframe').attr('src', 'https://www.youtube.com/embed/' + videoId);
                $('#video-preview').show();
            } else {
                $('#video-preview').hide();
            }
        } else {
            $('#video-preview').hide();
        }
    });

    // Extract YouTube Video ID
    function extractYouTubeID(url) {
        var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/;
        var match = url.match(regExp);
        return (match && match[7].length == 11) ? match[7] : false;
    }

    // Initialize TinyMCE for content editor
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#content',
            height: 300,
            plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak',
            toolbar_mode: 'floating',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
        });
    }
});
</script>
@stop