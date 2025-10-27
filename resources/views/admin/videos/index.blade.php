@extends('adminlte::page')

@section('title', 'Videos Management - Linhungdien.com')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Videos Management</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Videos</li>
        </ol>
    </div>
</div>
@stop

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <h3 class="card-title">Danh sách Videos</h3>
                            </div>
                            <div class="col-md-6 text-right">
                                <a href="{{ route('admin.videos.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Thêm Video
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Search and Filter Form -->
                        <form method="GET" class="mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Tìm kiếm video..." 
                                           value="{{ request('search') }}">
                                </div>
                                <div class="col-md-3">
                                    <select name="category_id" class="form-control">
                                        <option value="">Tất cả danh mục</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" 
                                                    {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="status" class="form-control">
                                        <option value="">Tất cả trạng thái</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                                        <option value="featured" {{ request('status') == 'featured' ? 'selected' : '' }}>Nổi bật</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Tìm
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Videos Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th width="100">Thumbnail</th>
                                        <th>Tiêu đề</th>
                                        <th>Danh mục</th>
                                        <th>Tác giả</th>
                                        <th width="100">Lượt xem</th>
                                        <th width="100">Trạng thái</th>
                                        <th width="150">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($videos as $video)
                                        <tr>
                                            <td>
                                                <img src="{{ $video->thumbnail_url }}" 
                                                     alt="{{ $video->title }}" 
                                                     class="img-thumbnail" 
                                                     style="width: 80px; height: 60px; object-fit: cover;">
                                            </td>
                                            <td>
                                                <strong>{{ $video->title }}</strong>
                                                @if($video->is_featured)
                                                    <span class="badge badge-warning ml-1">Nổi bật</span>
                                                @endif
                                                <br>
                                                <small class="text-muted">{{ $video->created_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $video->category->name }}</span>
                                            </td>
                                            <td>{{ $video->creator_name }}</td>
                                            <td>
                                                <span class="badge badge-secondary">{{ number_format($video->views_count) }}</span>
                                            </td>
                                            <td>
                                                @if($video->is_active)
                                                    <span class="badge badge-success">Hoạt động</span>
                                                @else
                                                    <span class="badge badge-danger">Tạm dừng</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ $video->youtube_url }}" target="_blank" 
                                                       class="btn btn-sm btn-info" title="Xem video">
                                                        <i class="fas fa-eye"></i>
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
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">Không có video nào</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($videos->hasPages())
                            <div class="d-flex justify-content-center">
                                {{ $videos->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop