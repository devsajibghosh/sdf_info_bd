@extends('admin.layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-3">@lang('Edit Payment Gateway') - {{ $paymentGateway->name }}</h3>

        <x-button href="{{ route('admin.payment_gateway.list') }}">
            <x-icons.back-v1 />
            @lang('Back')
        </x-button>
    </div>

    @php
        $class = \App\Helpers\GatewayHelper::paymentGateways($paymentGateway->key, true)['class'];
    @endphp

    <div class="card">

        <div class="card-body">

            <form action="{{ route('admin.payment_gateway.save', $paymentGateway->key) }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                <div class="row gy-4">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="image" class="form-label">@lang('Image')</label>
                            <input type="file" class="form-control" value="{{ old('image') }}" name="image" />
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="short_desc" class="form-label">@lang('Short Description')</label>
                            <textarea name="short_desc" id="short_desc" class="form-control">{{ $paymentGateway->short_desc }}</textarea>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="name" class="form-label">@lang('Name')</label>
                            <input type="text" class="form-control" value="{{ old('name', $paymentGateway->name) }}"
                                placeholder="@lang('Enter name')" name="name" />
                        </div>
                    </div>

                    @php
                        $gatewayConfigArray = json_decode(json_encode($paymentGateway->config), true) ?? [];
                    @endphp

                    @foreach ($class::filledConfig() as $inputField)
                        <div class="col-lg-4">
                            {!! $inputField !!}
                        </div>
                    @endforeach

                </div>

                <div class="d-flex justify-content-end mt-4">
                    <x-button type="submit">
                        <x-icons.save />
                        @lang('Save')
                    </x-button>
                </div>


            </form>

        </div>
    </div>
@endsection
