<form method="GET" class="search-form">
    <div class="input-group">
        <input 
            type="search" 
            name="search" 
            value="{{ request()->search }}" 
            class="form-control" 
            placeholder="@lang('Search...')" 
            @if(request()->search) autofocus @endif
        >

        @if(request()->search)
            <button type="button" class="btn btn-outline-danger clear-btn" onclick="document.querySelector('.search-form input[name=search]').value=''; this.form.submit();">
                &times;
            </button>
        @endif

        <x-button type="submit" class="btn btn-primary">
            <x-icons.search />
        </x-button>
    </div>
</form>
