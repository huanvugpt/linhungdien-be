@extends('adminlte::page')

@section('title', 'User Management - Linhungdien.com')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>User Management</h1>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('admin.users.pending') }}" class="btn btn-warning float-right mr-2">
                <i class="fas fa-clock"></i> Pending Users
            </a>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary float-right mr-2">
                <i class="fas fa-plus"></i> Add New User
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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Users List</h3>
            <div class="card-tools">
                <form action="{{ route('admin.users.index') }}" method="GET">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" name="search" class="form-control float-right" 
                               placeholder="Search users..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Created At</th>
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
                                @if($user->isApproved())
                                    <span class="badge badge-success">Approved</span>
                                @elseif($user->isPending())
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($user->isRejected())
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                
                                @if($user->isPending())
                                    <form action="{{ route('admin.users.approve', $user) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.users.reject', $user) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-sm" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @endif
                                
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" 
                                      style="display: inline-block;" 
                                      onsubmit="return confirm('Are you sure you want to delete this user?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages())
            <div class="card-footer clearfix">
                {{ $users->appends(request()->query())->links('pagination::bootstrap-4') }}
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