@props(['dataTable'])

<x-table.skeleton></x-table.skeleton>

{!! $dataTable->table(['class' => 'table table-striped dataTable']) !!}

@push('scripts')
    {!! $dataTable->scripts() !!}

    <script>
        var tableId = '#{{ $dataTable->getTableId() }}';

        $(tableId).on('processing.dt', function(e, settings, processing) {
            if (processing) {
                $('#custom-skeleton-loader').show();
                $(tableId + '_wrapper').hide();
            } else {
                $('#custom-skeleton-loader').hide();
                $(tableId + '_wrapper').show();
            }
        });
    </script>
@endpush
