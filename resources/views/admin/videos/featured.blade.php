@extends('admin.layouts.app')

@section('title', 'Videos Nổi Bật')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Videos Nổi Bật</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.videos.index') }}">Videos</a></li>
                    <li class="breadcrumb-item active">Nổi bật</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <h3 class="card-title">Danh sách Videos Nổi Bật</h3>
                            </div>
                            <div class="col-md-6 text-right">
                                <a href="{{ route('admin.videos.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-list"></i> Tất cả Videos
                                </a>
                                <a href="{{ route('admin.videos.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Thêm Video
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @if($videos->count() > 0)
                            <div class="row">
                                @foreach($videos as $video)
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card card-outline card-warning">
                                            <div class="position-relative">
                                                <img src="{{ $video->thumbnail_url }}" 
                                                     alt="{{ $video->title }}" 
                                                     class="card-img-top" 
                                                     style="height: 200px; object-fit: cover;">
                                                <div class="ribbon-wrapper ribbon-lg">
                                                    <div class="ribbon bg-warning">
                                                        Nổi bật
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="card-body">
                                                <h5 class="card-title">{{ Str::limit($video->title, 50) }}</h5>
                                                
                                                @if($video->excerpt)
                                                    <p class="card-text text-muted">
                                                        {{ Str::limit($video->excerpt, 100) }}
                                                    </p>
                                                @endif

                                                <div class="row text-center mb-3">
                                                    <div class="col">
                                                        <small class="text-muted">
                                                            <i class="fas fa-eye"></i> 
                                                            {{ number_format($video->views_count) }}
                                                        </small>
                                                    </div>
                                                    <div class="col">
                                                        <small class="text-muted">
                                                            <i class="fas fa-heart"></i> 
                                                            {{ number_format($video->likes_count) }}
                                                        </small>
                                                    </div>
                                                </div>

                                                <div class="mb-2">
                                                    <span class="badge badge-info">{{ $video->category->name }}</span>
                                                    @if($video->is_active)
                                                        <span class="badge badge-success">Hoạt động</span>
                                                    @else
                                                        <span class="badge badge-danger">Tạm dừng</span>
                                                    @endif
                                                </div>

                                                <small class="text-muted d-block mb-3">
                                                    <i class="fas fa-clock"></i> 
                                                    {{ $video->created_at->format('d/m/Y H:i') }}
                                                </small>
                                            </div>

                                            <div class="card-footer">
                                                <div class="btn-group w-100" role="group">
                                                    <a href="{{ $video->youtube_url }}" target="_blank" 
                                                       class="btn btn-sm btn-info" title="Xem video">
                                                        <i class="fas fa-play"></i>
                                                    </a>
                                                    <a href="{{ route('admin.videos.edit', $video) }}" 
                                                       class="btn btn-sm btn-warning" title="Sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.videos.destroy', $video) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                                onclick="return confirm('Bạn có chắc muốn xóa video này?')"
                                                                title="Xóa">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Pagination -->
                            @if($videos->hasPages())
                                <div class="d-flex justify-content-center">
                                    {{ $videos->links() }}
                                </div>
                            @endif
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-video fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">Chưa có video nổi bật nào</h4>
                                <p class="text-muted">Hãy đánh dấu một số video là "nổi bật" để hiển thị ở đây.</p>
                                <a href="{{ route('admin.videos.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Tạo Video Đầu Tiên
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.ribbon-wrapper.ribbon-lg .ribbon {
    width: 120px;
    right: -10px;
    top: 20px;
}
</style>
@endpush