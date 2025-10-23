@extends('adminlte::page')

@section('title', 'User Details - Linhungdien.com')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>User Details</h1>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary float-right">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">User Information</h3>
                </div>
                
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">ID</th>
                            <td>{{ $user->id }}</td>
                        </tr>
                        <tr>
                            <th>Full Name</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email Address</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Email Verified</th>
                            <td>
                                @if($user->email_verified_at)
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Verified
                                    </span>
                                    <br>
                                    <small class="text-muted">{{ $user->email_verified_at->format('d/m/Y H:i') }}</small>
                                @else
                                    <span class="badge badge-warning">
                                        <i class="fas fa-clock"></i> Not Verified
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td>{{ $user->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
                
                <div class="card-footer">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit User
                    </a>
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" 
                          style="display: inline-block;" 
                          onsubmit="return confirm('Are you sure you want to delete this user?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete User
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Statistics</h3>
                </div>
                
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-calendar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Account Age</span>
                            <span class="info-box-number">{{ $user->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Last Updated</span>
                            <span class="info-box-number">{{ $user->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
@stop

@section('js')
    {{-- Add here extra scripts --}}
@stop