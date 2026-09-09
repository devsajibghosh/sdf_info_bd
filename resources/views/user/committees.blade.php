@extends('user.layouts.main')


@section('content') 

    <div class="container py-5">
        <div class="row gy-3">
            @foreach ($committees as $committee)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card blog-card">
                        <a href="{{ route('user.committees.detail', $committee->id) }}" class="blog-card__thumb">
                            <img src="{{ asset('storage/' . $committee->image) }}" alt="{{ $committee->title }}" class="img-fluid" />
                        </a>

                        <div class="card-body">
                            <a class="blog-card__title d-block" href="{{ route('user.committees.detail', $committee->id) }}">
                                <h5 class="m-0">{{ $committee->name }}</h5>
                            </a>
                            <small>{{ $committee->title }}</small>
                            <br />
                            <small class="d-inline-block mb-2">{{ System::getDateTime($committee->created_at) }}</small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($committees->hasPages())
            {!! $committees->links() !!}
        @endif
    </div>
@endsection
