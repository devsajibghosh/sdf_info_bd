<div class="table-image-cell">
    <img src="{{ asset('assets/admin/images/user2.jpg') }}" class="img-fluid" />
    <div>
        <p class="m-0">{{ $payment->user->name }}</p>
        <small>{{ '@' . $payment->user->username }}</small>
    </div>
</div>