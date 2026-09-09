@extends('frontend.layouts.main')

@section('content')
    @if ($page->privacy)
        <div class="container pb-120 pt-5">
            <h1>{{ $page->title }}</h1>
            {!! $page->content !!}
        </div>
    @else
        @foreach (($sections ?? []) as $section)
            @include('frontend.sections.' . $section)
        @endforeach
    @endif
@endsection
