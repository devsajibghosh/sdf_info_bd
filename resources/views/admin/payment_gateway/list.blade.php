@extends('admin.layouts.app')

@section('content')
    <x-base.page-header>
        <x-slot name="title">
            {{ __($title) }}
        </x-slot>

        <x-slot name="right">
            {{-- <x-admin-search /> --}}

            @if (request()->routeIs('admin.payment_gateway.manual.list'))
                <x-button href="{{ route('admin.payment_gateway.new') }}">
                    <x-icons.add />
                    @lang('Add New')
                </x-button>
            @endif
        </x-slot>
    </x-base.page-header>

    @if ($paymentGateways->count())
        <div class="gateway-grid">
            @foreach ($paymentGateways as $gateway)
                <x-card class="gateway-card p-3">
                    <div class="badge-container d-flex gap-2">
                        <span class="badge text-bg-{{ $gateway->status == 1 ? 'success' : 'danger' }}">
                            {{ ucfirst($gateway->status == 1 ? 'Active' : 'Inactive') }}
                        </span>
                        <span class="badge text-bg-{{ !$gateway->is_test_mode ? 'danger' : 'success' }}">
                            {{ ucfirst(!$gateway->is_test_mode ? 'Test Mode' : 'Live') }}
                        </span>
                    </div>

                    <div>
                        <img class="img-fluid" src="{{ $gateway->image_url }}" />
                        <p class="gateway-card__title">{{ $gateway->name }}</p>
                    </div>

                    <div class="text-muted small mb-2">
                        {{ __($gateway->short_desc ?? 'No Description') }}
                    </div>

                    <x-button class="mt-2" href="{{ route('admin.payment_gateway.edit', $gateway->key) }}">
                        <x-icons.edit />
                        @lang('Edit')
                    </x-button>

                </x-card>
            @endforeach
        </div>
    @else
        <div class="alert alert-info mt-4">
            @lang('No payment gateways found.')
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .gateway-card {
            text-align: center;
            transition: all 0.3s ease;
            border: 0;
            box-shadow: rgba(0, 0, 0, 0.1) 0px 0px 5px 0px, rgba(0, 0, 0, 0.1) 0px 0px 1px 0px;
        }

        .gateway-card .badge-container {
            margin-bottom: 10px;
            justify-content: space-between;
            display: flex;
        }

        .gateway-card .gateway-card__title {
            margin-top: 10px;
            font-size: 1.2rem;
        }

        .gateway-card .card-body {
            padding-top: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .gateway-card img {
            width: 75px;
        }

        .gateway-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .gateway-icon {
            font-size: 2rem;
        }

        .gateway-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }
    </style>
@endpush
