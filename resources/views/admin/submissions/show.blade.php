@extends('adminlte::page')

@section('title', 'Chi tiết bài viết đóng góp')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Chi tiết bài viết đóng góp</h1>
        <a href="{{ route('admin.submissions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ $postSubmission->title }}</h3>
                    @switch($postSubmission->status)
                        @case('pending')
                            <span class="badge badge-warning badge-lg">Chờ duyệt</span>
                            @break
                        @case('approved')
                            <span class="badge badge-success badge-lg">Đã duyệt</span>
                            @break
                        @case('rejected')
                            <span class="badge badge-danger badge-lg">Từ chối</span>
                            @break
                    @endswitch
                </div>
                
                <div class="card-body">
                    @if($postSubmission->featured_image)
                        <div class="text-center mb-4">
                            <img src="{{ Storage::url($postSubmission->featured_image) }}" 
                                 alt="Featured Image" class="img-fluid rounded" 
                                 style="max-height: 300px; object-fit: cover;">
                        </div>
                    @endif

                    @if($postSubmission->excerpt)
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> Tóm tắt:</h5>
                            {{ $postSubmission->excerpt }}
                        </div>
                    @endif

                    <div class="content">
                        {!! nl2br(e($postSubmission->content)) !!}
                    </div>

                    @if($postSubmission->images && count($postSubmission->images) > 0)
                        <hr>
                        <h5><i class="fas fa-images"></i> Hình ảnh bổ sung:</h5>
                        <div class="row">
                            @foreach($postSubmission->images as $image)
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <img src="{{ Storage::url($image) }}" 
                                         alt="Additional Image" class="img-fluid rounded shadow-sm">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Submission Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thông tin bài viết</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Tác giả:</dt>
                        <dd class="col-sm-8">
                            <strong>{{ $postSubmission->user->name }}</strong><br>
                            <small class="text-muted">{{ $postSubmission->user->email }}</small>
                        </dd>

                        <dt class="col-sm-4">Danh mục:</dt>
                        <dd class="col-sm-8">
                            <span class="badge badge-info">{{ $postSubmission->category->name }}</span>
                        </dd>

                        <dt class="col-sm-4">Ngày gửi:</dt>
                        <dd class="col-sm-8">{{ $postSubmission->submitted_at->format('d/m/Y H:i') }}</dd>

                        @if($postSubmission->reviewer)
                            <dt class="col-sm-4">Người duyệt:</dt>
                            <dd class="col-sm-8">{{ $postSubmission->reviewer->name }}</dd>

                            <dt class="col-sm-4">Ngày duyệt:</dt>
                            <dd class="col-sm-8">{{ $postSubmission->reviewed_at->format('d/m/Y H:i') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Admin Note -->
            @if($postSubmission->admin_note)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ghi chú từ quản trị viên</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            {{ $postSubmission->admin_note }}
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            @if($postSubmission->status === 'pending')
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Hành động</h3>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-success btn-block mb-2" 
                                data-toggle="modal" data-target="#approveModal">
                            <i class="fas fa-check"></i> Phê duyệt bài viết
                        </button>
                        <button type="button" class="btn btn-danger btn-block" 
                                data-toggle="modal" data-target="#rejectModal">
                            <i class="fas fa-times"></i> Từ chối bài viết
                        </button>
                    </div>
                </div>
            @elseif($postSubmission->status === 'approved')
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Hành động</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.submissions.publish', $postSubmission) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-block"
                                    onclick="return confirm('Bạn có chắc muốn đăng bài viết này?')">
                                <i class="fas fa-globe"></i> Đăng bài viết
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Delete -->
            <div class="card border-danger">
                <div class="card-header bg-danger">
                    <h3 class="card-title text-white">Khu vực nguy hiểm</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.submissions.destroy', $postSubmission) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-block"
                                onclick="return confirm('Bạn có chắc muốn xóa bài viết này? Hành động này không thể hoàn tác!')">
                            <i class="fas fa-trash"></i> Xóa bài viết
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    @if($postSubmission->status === 'pending')
        <div class="modal fade" id="approveModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.submissions.approve', $postSubmission) }}">
                        @csrf
                        <div class="modal-header">
                            <h4 class="modal-title">Phê duyệt bài viết</h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Bạn đang phê duyệt bài viết: <strong>{{ $postSubmission->title }}</strong>
                            </div>
                            
                            <div class="form-group">
                                <label>Ghi chú cho tác giả (không bắt buộc):</label>
                                <textarea name="admin_note" class="form-control" rows="4" 
                                          placeholder="Nhập ghi chú tích cực cho tác giả..."></textarea>
                                <small class="text-muted">Ghi chú này sẽ được gửi trong thông báo đến tác giả</small>
                            </div>
                            
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="publish_immediately" value="1" checked>
                                <label class="form-check-label">
                                    <strong>Đăng bài ngay lập tức</strong>
                                    <br><small class="text-muted">Nếu bỏ chọn, bài viết sẽ chỉ được phê duyệt nhưng chưa đăng công khai</small>
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Phê duyệt
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.submissions.reject', $postSubmission) }}">
                        @csrf
                        <div class="modal-header">
                            <h4 class="modal-title">Từ chối bài viết</h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Bạn đang từ chối bài viết: <strong>{{ $postSubmission->title }}</strong>
                            </div>
                            
                            <div class="form-group">
                                <label>Lý do từ chối <span class="text-danger">*</span>:</label>
                                <textarea name="admin_note" class="form-control" rows="5" required
                                          placeholder="Nhập lý do cụ thể tại sao bài viết bị từ chối..."></textarea>
                                <small class="text-muted">
                                    Lý do này sẽ được gửi đến tác giả. Hãy viết một cách rõ ràng và mang tính xây dựng để tác giả có thể cải thiện bài viết.
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times"></i> Từ chối
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@stop

@section('css')
    <style>
        .content {
            line-height: 1.6;
            font-size: 16px;
        }
        .badge-lg {
            font-size: 14px;
            padding: 8px 12px;
        }
        .shadow-sm {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
    </style>
@stop

@section('js')
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert:not(.alert-info):not(.alert-warning)').fadeOut();
        }, 5000);
    </script>
@stop