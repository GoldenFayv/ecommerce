@extends('mails.base')

@section('title', 'Verify Your Email')

@section('email-title', 'Email Verification')
{{-- @section('email-title')
    {{ $data['email_title'] }}
@endsection --}}

@section('content')
    <p>Hi {{ $data['name'] }},</p>
    <p>
        We're excited to have you in the Brand Name Family! Your account has been successfully created.
    </p>
    <p>Please use the code below to verify your email address:</p>
    <h2 style="color: #6576ff;">{{ $data['code'] }}</h2>
    <p><strong>Note:</strong> The code expires in 10 minutes.</p>
@endsection
