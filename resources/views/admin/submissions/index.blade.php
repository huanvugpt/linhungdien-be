@extends('adminlte::page')

@section('title', 'Quản lý đóng góp bài viết')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Quản lý đóng góp bài viết</h1>
        <div>
            <span class="badge badge-warning mr-2">Chờ duyệt: {{ $submissions->where('status', 'pending')->count() }}</span>
            <span class="badge badge-success mr-2">Đã duyệt: {{ $submissions->where('status', 'approved')->count() }}</span>
            <span class="badge badge-danger">Từ chối: {{ $submissions->where('status', 'rejected')->count() }}</span>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách bài viết đóng góp</h3>
                    <div class="card-tools">
                        <form method="GET" class="d-flex">
                            <select name="status" class="form-control form-control-sm mr-2">
                                <option value="">Tất cả trạng thái</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Từ chối</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">Lọc</button>
                        </form>
                    </div>
                </div>

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tiêu đề</th>
                                <th>Tác giả</th>
                                <th>Danh mục</th>
                                <th>Trạng thái</th>
                                <th>Ngày gửi</th>
                                <th>Người duyệt</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions->items() as $submission)
                                <tr>
                                    <td>{{ $submission->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($submission->featured_image)
                                                <img src="{{ Storage::url($submission->featured_image) }}" 
                                                     alt="Featured" class="img-thumbnail mr-2" 
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @endif
                                            <div>
                                                <strong>{{ Str::limit($submission->title, 40) }}</strong>
                                                @if($submission->excerpt)
                                                    <br><small class="text-muted">{{ Str::limit($submission->excerpt, 60) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="font-weight-bold">{{ $submission->user->name }}</span><br>
                                        <small class="text-muted">{{ $submission->user->email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $submission->category->name }}</span>
                                    </td>
                                    <td>
                                        @switch($submission->status)
                                            @case('pending')
                                                <span class="badge badge-warning">Chờ duyệt</span>
                                                @break
                                            @case('approved')
                                                <span class="badge badge-success">Đã duyệt</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge badge-danger">Từ chối</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ $submission->submitted_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($submission->reviewer)
                                            <small>{{ $submission->reviewer->name }}</small><br>
                                            <small class="text-muted">{{ $submission->reviewed_at->format('d/m/Y H:i') }}</small>
                                        @else
                                            <span class="text-muted">Chưa duyệt</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.submissions.show', $submission) }}" 
                                               class="btn btn-info btn-sm" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($submission->status === 'pending')
                                                <button type="button" class="btn btn-success btn-sm" 
                                                        data-toggle="modal" data-target="#approveModal{{ $submission->id }}"
                                                        title="Phê duyệt">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        data-toggle="modal" data-target="#rejectModal{{ $submission->id }}"
                                                        title="Từ chối">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @elseif($submission->status === 'approved')
                                                <form method="POST" action="{{ route('admin.submissions.publish', $submission) }}" 
                                                      style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-sm" 
                                                            title="Đăng bài"
                                                            onclick="return confirm('Bạn có chắc muốn đăng bài viết này?')">
                                                        <i class="fas fa-globe"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <form method="POST" action="{{ route('admin.submissions.destroy', $submission) }}" 
                                                  style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                        title="Xóa"
                                                        onclick="return confirm('Bạn có chắc muốn xóa bài viết này?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Approve Modal -->
                                <div class="modal fade" id="approveModal{{ $submission->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.submissions.approve', $submission) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Phê duyệt bài viết</h4>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Tiêu đề:</strong> {{ $submission->title }}</p>
                                                    <div class="form-group">
                                                        <label>Ghi chú cho tác giả (không bắt buộc):</label>
                                                        <textarea name="admin_note" class="form-control" rows="3" 
                                                                  placeholder="Nhập ghi chú cho tác giả..."></textarea>
                                                    </div>
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" name="publish_immediately" value="1" checked>
                                                        <label class="form-check-label">Đăng bài ngay lập tức</label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-success">Phê duyệt</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal{{ $submission->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.submissions.reject', $submission) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Từ chối bài viết</h4>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Tiêu đề:</strong> {{ $submission->title }}</p>
                                                    <div class="form-group">
                                                        <label>Lý do từ chối <span class="text-danger">*</span>:</label>
                                                        <textarea name="admin_note" class="form-control" rows="4" required
                                                                  placeholder="Nhập lý do từ chối bài viết..."></textarea>
                                                        <small class="text-muted">Lý do này sẽ được gửi đến tác giả</small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-danger">Từ chối</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i><br>
                                        <span class="text-muted">Không có bài viết đóng góp nào</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($submissions->hasPages())
                    <div class="card-footer">
                        {{ $submissions->links('vendor.pagination.bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table td {
            vertical-align: middle;
        }
        .img-thumbnail {
            border-radius: 4px;
        }
    </style>
@stop

@section('js')
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    </script>
@stop