@extends('admin.layouts.settings')

@section('panel')
    <div class="container-fluid">
        <div class="row">
            @foreach ($serverInformation as $sectionTitle => $infoItems)
                <div class="col-xl-6 col-lg-12 mb-4">
                    <x-card>
                        <div class="card-header-custom">{{ $sectionTitle }}</div>
                        
                        <div class="card-body-custom p-3">
                            @if (empty($infoItems))
                                <p class="text-muted">@lang('No information available for this section.')</p>
                            @else
                                <div class="table-responsive">
                                    <table class="info-table table table-bordered table-sm">
                                        <tbody>
                                            @php $itemCount = 0; @endphp
                                            @foreach ($infoItems as $key => $value)
                                                @if ($itemCount < 10 || $sectionTitle === 'PHP Configuration')
                                                    @php
                                                        $valueClass = '';
                                                        if (is_bool($value)) {
                                                            $displayValue = $value ? __('Yes') : __('No');
                                                            $valueClass = $value ? 'status-yes' : 'status-no';
                                                        } elseif (is_string($value)) {
                                                            $lowerVal = strtolower($value);
                                                            if (in_array($lowerVal, ['enabled', 'yes', 'connected'])) {
                                                                $valueClass = 'status-enabled';
                                                            } elseif (in_array($lowerVal, ['disabled', 'no'])) {
                                                                $valueClass = 'status-disabled';
                                                            } elseif (
                                                                str_contains($key, 'error') ||
                                                                str_contains($value, 'error') ||
                                                                str_contains($value, 'could not connect')
                                                            ) {
                                                                $valueClass = 'status-error';
                                                            } elseif (
                                                                str_contains($key, 'debug mode') &&
                                                                $lowerVal === 'enabled'
                                                            ) {
                                                                $valueClass = 'status-warning';
                                                            }
                                                            $displayValue = __($value);
                                                        } elseif (is_null($value)) {
                                                            $displayValue = __('N/A');
                                                        } else {
                                                            $displayValue = is_array($value)
                                                                ? json_encode($value, JSON_PRETTY_PRINT)
                                                                : __((string) $value);
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <th class="w-40">{{ __($key) }}</th>
                                                        <td class="{{ $valueClass }}">{{ $displayValue }}</td>
                                                    </tr>
                                                    @php $itemCount++; @endphp
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </x-card>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .card-header-custom {
            background: #f1f1f1;
            border-bottom: 1px solid #ddd;
            padding: 0.75rem 1.25rem;
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
        }

        .info-table th,
        .info-table td {
            padding: 8px 10px;
            font-size: 0.9rem;
            word-break: break-word;
            vertical-align: top;
        }

        .info-table td.status-enabled,
        .info-table td.status-yes,
        .info-table td.status-connected {
            color: #28a745;
            font-weight: bold;
        }

        .info-table td.status-disabled,
        .info-table td.status-no,
        .info-table td.status-error {
            color: #dc3545;
            font-weight: bold;
        }

        .info-table td.status-warning {
            color: #ffc107;
            font-weight: bold;
        }

        @media (max-width: 768px) {

            .info-table th,
            .info-table td {
                font-size: 0.85rem;
            }
        }
    </style>
@endpush
