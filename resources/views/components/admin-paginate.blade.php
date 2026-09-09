@props([
    'model'
])

@if ($model->hasPages())
    <div class="mt-3">
        @php echo $model->links() @endphp
    </div>
@endif
