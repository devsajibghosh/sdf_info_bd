@extends('frontend.layouts.main')

@php
    $projects = \App\Models\Project::get();
@endphp

@section('content')
    <div class="container py-5">
        <div class="row">
            <div class="col-12 col-md-4 mb-3 mb-md-0">
                <div class="nav flex-column nav-pills custom-tab-nav" id="v-pills-tab" role="tablist"
                    aria-orientation="vertical">
                    @foreach ($projects as $project)
                        <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="tab-{{ $loop->index }}-tab"
                            data-bs-toggle="pill" data-bs-target="#tab-{{ $loop->index }}" type="button" role="tab"
                            aria-controls="tab-{{ $loop->index }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                            {{ __($project->title) }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="col-12 col-md-8">
                <div class="tab-content border rounded p-4 bg-white shadow-sm" id="v-pills-tabContent">
                    @foreach ($projects as $project)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $loop->index }}"
                            role="tabpanel" aria-labelledby="tab-{{ $loop->index }}-tab">
                            <img class="img-fluid mb-2" src="{{ imageSrc($project->image) }}" />
                            <div class="desc">
                                <h5 class="mt-1 mb-2">@lang('Posted at') : {{ $project->created_at->format('Y-m-d H:i:s') }}</h5>
                                
                                @php echo $project->details @endphp
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .desc p {
            text-align: justify;
        }
        
        .custom-tab-nav .nav-link {
            border: 1px solid hsl(var(--base));
            border-left: 4px solid transparent;
            color: #495057;
            font-weight: 500;
            background-color: #f8f9fa;
            margin-bottom: 5px;
            transition: all 0.2s ease-in-out;
            text-align: left;
        }

        .custom-tab-nav .nav-link:hover {
            background-color: #e9ecef;
        }

        .custom-tab-nav .nav-link.active {
            border-left-color: hsl(var(--base));
            background-color: #fff;
            color: hsl(var(--base));
            box-shadow: 0 0 0 2px hsl(var(--base));
        }

        .tab-content {
            min-height: 250px;
        }
    </style>
@endpush
