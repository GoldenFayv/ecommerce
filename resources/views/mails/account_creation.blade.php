@extends('mails.base')

@section('title', 'Verify Your Email')

@section('email-title', 'Account Creation')
{{-- @section('email-title')
    {{ $data['email_title'] }}
@endsection --}}

@section('content')
    <p>Hi {{ $data['name'] }},</p>
    <p>
        This brandname account was created for you upon your request.
    </p>
    <p>Please use the email address and password below to log in</p>
    <h2 style="color: #436a2a;">Email: {{ $data['email'] }}</h2>
    <h2 style="color: #3a4071;">Password: {{ $data['password'] }}</h2>
    {{-- <p><strong>Note:</strong> The code expires in 10 minutes.</p> --}}
@endsection
