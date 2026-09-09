@extends('user.layouts.main')


@section('content')
    <!--<x-breadcrumb title="Committee Details" />-->

    <div class="container py-5">
        <div class="card blog-details-card">
            <img src="{{ asset('storage/' . $committee->image) }}" alt="{{ $committee->name }}" class="img-fluid" />
    
            <div class="card-body">
                <h1 class="mb-0">{{ $committee->name }}</h1>
                <small class="d-block mb-1">{{$committee->title}}</small><br/>
                <small class="d-inline-block mb-2">{{ System::getDateTime($committee->created_at) }}</small>
    
                {!! $committee->description !!}
            </div>
        </div>
    </div>
@endsection
