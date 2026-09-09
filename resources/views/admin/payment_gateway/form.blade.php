@extends('admin.layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-3">{{ $title }}</h3>

        <x-button href="{{ route('admin.payment_gateway.manual.list') }}">
            <x-icons.back-v1 />
            @lang('Back')
        </x-button>
    </div>

    <div class="card">

        <div class="card-body">

            <form action="{{ route('admin.payment_gateway.save', $paymentGateway?->key ?? null) }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                <div class="row gy-4">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>@lang('Image')</label>
                            <input type="file" class="form-control" name="image" />
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>@lang('Name')</label>
                            <input type="text" class="form-control"
                                value="{{ old('name', $paymentGateway->name ?? '') }}" placeholder="@lang('Enter name')"
                                name="name" />
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>@lang('Short Description')</label>
                            <input type="text" class="form-control"
                                value="{{ old('short_desc', $paymentGateway->short_desc ?? '') }}"
                                placeholder="@lang('Enter a short desc')" name="short_desc" />
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <label class="form-label">@lang('Instruction')</label>
                        <textarea class="form-control" name="instruction" id="instruction-editor" rows="6"
                            placeholder="@lang('Enter a short description for the payment gateway')">{{ old('body', $paymentGateway->instruction ?? '') }}</textarea>
                    </div>
                </div>

                {{-- FORM BUILDING STARTS --}}
                {{-- <div class="col-lg-12">
                    <button class="btn addRowBtn btn-primary mt-2" type="button">@lang('Add New')</button>

                    <table class="table mt-3 form-fields-table">
                        <thead class="bg-primary">
                            <tr class="form-fields__item">
                                <th>@lang('Label')</th>
                                <th>@lang('Type')</th>
                                <th>@lang('Required')</th>
                                <th>@lang('Instruction')</th>
                            </tr>
                        </thead>
                        <tbody class="form-fields-table__items">
                            <tr class="form-fields-table__item">
                                <td>Label</td>
                                <td>Text</td>
                                <td>Yes</td>
                                <td>Yes</td>
                            </tr>
                        </tbody>
                    </table>
                </div> --}}
                {{-- FORM BUILDING ENDS --}}

                <div class="d-flex justify-content-end mt-4">
                    <x-button type="submit">
                        <x-icons.save />
                        @lang('Save')
                    </x-button>
                </div>


                <div class="hidden_fields">

                </div>
                {{-- <input type="hidden" name="fields" /> --}}
            </form>

        </div>
    </div>

    <!-- the modal of the form builder -->
    <div class="modal fade" id="formBuilderModal" tabindex="-1" aria-labelledby="formBuilderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="formBuilderModalLabel">@lang('Add New Field')</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="formBuilderForm">
                    <div class="modal-body">
                        <x-form.group>
                            <label>@lang('Label')</label>
                            <input class="form-control" name="label" />
                        </x-form.group>

                        <x-form.group>
                            <label>@lang('Type')</label>
                            <select class="form-control" name="type">
                                <option value="text">@lang('Text')</option>
                                <option value="number">@lang('Number')</option>
                                <option value="select">@lang('Select')</option>
                                <option value="file">@lang('File')</option>
                            </select>
                        </x-form.group>

                        <x-form.group>
                            <label>@lang('Required')</label>
                            <select class="form-control" name="required">
                                <option value="1">@lang('Yes')</option>
                                <option value="0">@lang('No')</option>
                            </select>
                        </x-form.group>

                        <x-form.group>
                            <label>@lang('Instruction')</label>
                            <input class="form-control" name="instruction" />
                        </x-form.group>
                    </div>
                    <div class="modal-footer">
                        <x-button class="formBuilderFormButton" type="button">@lang('Submit')</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- the modal ends here -->
@endsection

@push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/12.3.1/classic/ckeditor.js"></script>

    <script>
        SystemHelper.initEditor('#instruction-editor');

        $('.addRowBtn').on('click', function() {
            $('#formBuilderModal').modal('show');
        });

        let fields = "";
        
        // when the builder form submit(takes the inputs there in the main form to submit)
        $(document).on('click', '.formBuilderFormButton', function() {
            const form = $('.formBuilderForm');
            
            form.serializeArray().map(function(value, index) {
                const inputField = `<input type="hidden" name="fields[${value.name}]" value="fields[]${value.value}" />`;
                fields += inputField;
            });

            $('.hidden_fields').append(fields);
        });
    </script>
@endpush
