@if (session('success'))
    @push('scripts')
        <script>
            $.jGrowl("{{ session('success') }}", {
                header: 'Success',
                theme: 'jgrowl-success'
            }, { life: 5000 });
        </script>
    @endpush
@endif

@if (session('error'))
    @push('scripts')
        <script>
            $.jGrowl("{{ session('error') }}", {
                header: 'Error',
                theme: 'jgrowl-error'
            }, { life: 5000 });
        </script>
    @endpush
@endif

@if (session('info'))
    @push('scripts')
        <script>
            $(document).ready(function() {
                $.jGrowl("{{ session('info') }}", {
                    header: 'Info',
                    theme: 'jgrowl-info'
                }, { life: 5000 });
            });
        </script>
    @endpush
@endif


@if ($errors->any())
    @push('scripts')
        <script>
            $(document).ready(function() {
                @foreach ($errors->all() as $error)
                    $.jGrowl(@json($error), {
                        header: 'Validation Error',
                        theme: 'jgrowl-error'
                    }, { life: 5000 });
                @endforeach
            });
        </script>
    @endpush
@endif


{{-- @if (isset($errors) && $errors->any())
    @push('scripts')
        <script>
            $.jGrowl("{{ session('info') }}", {
                header: 'Info',
                theme: 'jgrowl-info'
            });
        </script>
    @endpush

    <div class="alert alert-error">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif --}}
