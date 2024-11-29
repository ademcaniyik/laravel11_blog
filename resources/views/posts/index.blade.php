@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h1>Blog Posts</h1>
        @auth
            <a href="{{ route('posts.create') }}" class="btn btn-primary mb-3">Create New Post</a>
        @endauth

        @foreach($posts as $post)
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">{{ $post->title }}</h5>
                    <h6 class="card-subtitle mb-2 text-muted">By {{ $post->user->name }} on {{ $post->created_at->format('M d, Y') }}</h6>
                    <p class="card-text">{{ Str::limit($post->content, 200) }}</p>
                    <a href="{{ route('posts.show', $post) }}" class="btn btn-primary">Read More</a>
                </div>
            </div>
        @endforeach

        {{ $posts->links() }}
    </div>
</div>
@endsection
