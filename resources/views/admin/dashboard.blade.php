@extends('admin.layouts.master')

@section('title', 'Dashboard - Linhungdien.com')

@section('content_header')
    <h1>Admin Dashboard</h1>
@stop

@section('content')
    <!-- Posts Statistics Row -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['total_posts'] }}</h3>
                    <p>Total Posts</p>
                </div>
                <div class="icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <a href="{{ route('admin.posts.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['published_posts'] }}</h3>
                    <p>Published Posts</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="{{ route('admin.posts.index', ['status' => 'published']) }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['pending_posts'] }}</h3>
                    <p>Pending Posts</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <a href="{{ route('admin.posts.pending') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($stats['total_views']) }}</h3>
                    <p>Total Views</p>
                </div>
                <div class="icon">
                    <i class="fas fa-eye"></i>
                </div>
                <a href="{{ route('admin.posts.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Users Statistics Row -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_users'] }}</h3>
                    <p>Total Users</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('admin.users.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['pending_users'] }}</h3>
                    <p>Pending Users</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-clock"></i>
                </div>
                <a href="{{ route('admin.users.pending') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['total_categories'] }}</h3>
                    <p>Categories</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tags"></i>
                </div>
                <a href="{{ route('admin.categories.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($stats['total_likes']) }}</h3>
                    <p>Total Likes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-heart"></i>
                </div>
                <a href="{{ route('admin.posts.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Posts</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.posts.index') }}" class="btn btn-tool">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_posts as $post)
                                <tr>
                                    <td>
                                        @if($post->is_featured)
                                            <i class="fas fa-star text-warning" title="Featured"></i>
                                        @endif
                                        <a href="{{ route('admin.posts.show', $post) }}">
                                            {{ Str::limit($post->title, 30) }}
                                        </a>
                                    </td>
                                    <td>
                                        @switch($post->status)
                                            @case('draft')
                                                <span class="badge badge-secondary">Draft</span>
                                                @break
                                            @case('pending')
                                                <span class="badge badge-warning">Pending</span>
                                                @break
                                            @case('published')
                                                <span class="badge badge-success">Published</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge badge-danger">Rejected</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td><span class="badge badge-info">{{ $post->views_count }}</span></td>
                                    <td>{{ $post->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No recent posts</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Popular Posts</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.posts.index') }}" class="btn btn-tool">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Views</th>
                                <th>Likes</th>
                                <th>Published</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($popular_posts as $post)
                                <tr>
                                    <td>
                                        @if($post->is_featured)
                                            <i class="fas fa-star text-warning" title="Featured"></i>
                                        @endif
                                        <a href="{{ route('admin.posts.show', $post) }}">
                                            {{ Str::limit($post->title, 30) }}
                                        </a>
                                    </td>
                                    <td><span class="badge badge-info">{{ number_format($post->views_count) }}</span></td>
                                    <td><span class="badge badge-danger">{{ $post->likes_count }}</span></td>
                                    <td>{{ $post->published_at ? $post->published_at->format('d/m/Y') : 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No popular posts</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent User Registrations</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Registration Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_users as $user)
                                <tr>
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No recent registrations</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <p>Logged in as: <strong>{{ Auth::guard('admin')->user()->name }}</strong></p>
                    
                    <h5>Content Management:</h5>
                    <div class="btn-group-vertical d-block mb-3">
                        <a href="{{ route('admin.posts.create') }}" class="btn btn-primary btn-block mb-2">
                            <i class="fas fa-plus"></i> Create New Post
                        </a>
                        <a href="{{ route('admin.posts.pending') }}" class="btn btn-warning btn-block mb-2">
                            <i class="fas fa-clock"></i> Review Pending Posts ({{ $stats['pending_posts'] }})
                        </a>
                        <a href="{{ route('admin.categories.create') }}" class="btn btn-info btn-block mb-2">
                            <i class="fas fa-tags"></i> Add New Category
                        </a>
                    </div>
                    
                    <h5>User Management:</h5>
                    <div class="btn-group-vertical d-block">
                        <a href="{{ route('admin.users.pending') }}" class="btn btn-warning btn-block mb-2">
                            <i class="fas fa-user-clock"></i> Review Pending Users ({{ $stats['pending_users'] }})
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-users"></i> Manage All Users
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop