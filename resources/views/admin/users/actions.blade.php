<div class="d-flex gap-2 justify-content-end">
    <x-button href="{{ route('admin.user.details', $user->id) }}" class="btn-sm">
        <x-icons.package-open />
        @lang('Details')
    </x-button>
    
    <x-button confirmDelete href="{{ route('admin.user.delete', $user->id) }}" class="btn-danger btn-sm">
        <x-icons.delete-v2 />
        @lang('Delete')
    </x-button>
</div>