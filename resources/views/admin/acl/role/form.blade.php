@extends('admin.layouts.app')

@section('content')
    <x-page-header 
        :page_title="isset($role) ? __('Edit Role') : __('Create Role')" 
        :back_route="route('admin.acl.role.list')"
    />

    <div class="card">
        <div class="card-body">

            <form method="POST" action="{{ route('admin.acl.role.store', @$role->id) }}">
                @csrf

                <x-form.group>
                    <label>@lang('Role Name')</label>
                    <input type="text" name="name" class="form-control" placeholder="@lang('Enter a unique role name')" 
                        value="{{ old('name', @$role->name) }}" />
                    <small class="text-muted d-block mt-1">@lang('This should be a unique system name like super-admin, editor, etc.')</small>
                </x-form.group>

                <x-form.group>
                    <label>@lang('Title')</label>
                    <input type="text" name="title" class="form-control" placeholder="@lang('Enter display title')" 
                        value="{{ old('title', @$role->title) }}" />
                    <small class="text-muted d-block mt-1">@lang('This title will be shown in the UI')</small>
                </x-form.group>

                <x-form.group>
                    <label>@lang('Abilities')</label>
                    
                    <div class="row">
                        @foreach ($abilities->groupBy(function($a) {
                            return explode('.', $a->name)[0]; // Group like: settings.*, reports.*, etc.
                        }) as $group => $groupedAbilities)
                            <div class="col-md-3 mb-3">
                                <strong class="d-block mb-2 text-uppercase text-muted">{{ str_replace('-', ' ', $group) }}</strong>
                                
                                @foreach ($groupedAbilities as $ability)
                                    <div class="form-check mb-1">
                                        <input 
                                            class="form-check-input" 
                                            type="checkbox" 
                                            name="abilities[]" 
                                            value="{{ $ability->id }}"
                                            id="ability_{{ $ability->id }}"
                                            {{ isset($role) && $role->abilities->contains('id', $ability->id) ? 'checked' : '' }}
                                        >
                                        <label class="form-check-label" for="ability_{{ $ability->id }}">
                                            {{ ucfirst($ability->title ?? $ability->name) }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </x-form.group>

                <div class="text-end">
                    <x-button type="submit">
                        <x-icons.save />
                        @lang('Submit')
                    </x-button>
                </div>
            </form>

        </div>
    </div>
@endsection

@push('styles')
<style>
    .ability-group {
        border: 1px solid #e5e7eb;
        padding: 1rem;
        border-radius: .5rem;
        background: #f9fafb;
        transition: 0.2s ease;
    }

    .ability-group:hover {
        background: #f1f5f9;
    }

    .ability-group-title {
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 0.75rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .select-toggle {
        font-size: 12px;
        color: #3b82f6;
        cursor: pointer;
        text-decoration: underline;
    }

    .form-check {
        padding-left: 1.5rem;
        font-size: 14px;
    }

    .form-check-input {
        margin-left: -1.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
    $(function () {
        $('.select-toggle').on('click', function () {
            const $toggle = $(this);
            const $group = $toggle.closest('.ability-group');
            const checkboxes = $group.find('input[type="checkbox"]');

            const allChecked = checkboxes.filter(':checked').length === checkboxes.length;

            checkboxes.prop('checked', !allChecked);
            $toggle.text(allChecked ? 'Select All' : 'Unselect All');
        });
    });
</script>
@endpush
