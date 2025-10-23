@extends('adminlte::page')

@section('title', 'Pending Users - Linhungdien.com')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Pending Users</h1>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary float-right">
                <i class="fas fa-arrow-left"></i> Back to All Users
            </a>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> Success!</h5>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-ban"></i> Error!</h5>
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Users Pending Approval</h3>
        </div>
        
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Provider</th>
                        <th>Registration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name ?? 'N/A' }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->provider)
                                    <span class="badge badge-info">{{ ucfirst($user->provider) }}</span>
                                @else
                                    <span class="badge badge-secondary">Local</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                
                                <form action="{{ route('admin.users.approve', $user) }}" method="POST" style="display: inline-block;" 
                                      onsubmit="return confirm('Are you sure you want to approve this user?')">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                
                                <form action="{{ route('admin.users.reject', $user) }}" method="POST" style="display: inline-block;"
                                      onsubmit="return confirm('Are you sure you want to reject this user?')">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="text-muted py-4">
                                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                                    <br>
                                    No pending users found. All users are approved or rejected.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages())
            <div class="card-footer clearfix">
                {{ $users->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
@stop

@section('js')
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
@stop