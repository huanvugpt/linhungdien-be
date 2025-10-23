@extends('adminlte::page')

@section('title', 'Create User - Linhungdien.com')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Create New User</h1>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary float-right">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">User Information</h3>
        </div>
        
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           placeholder="Enter full name"
                           value="{{ old('name') }}" 
                           required>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           placeholder="Enter email address"
                           value="{{ old('email') }}" 
                           required>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           placeholder="Enter password"
                           required>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" 
                           class="form-control" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           placeholder="Confirm password"
                           required>
                </div>
            </div>
            
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create User
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-default">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
@stop

@section('js')
    {{-- Add here extra scripts --}}
@stop