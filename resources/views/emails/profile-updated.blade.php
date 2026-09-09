@extends('emails.layout')

@section('content')
    <h2>Hello {{ $user->name }}</h2>
    <p>Your profile has been successfully updated.</p>
@endsection
