<x-button confirmDelete href="{{ route('admin.report.payment.delete', $payment->id) }}" class="btn-danger btn-sm">
    <x-icons.delete-v2 />
    @lang('Delete')</a>
</x-button>