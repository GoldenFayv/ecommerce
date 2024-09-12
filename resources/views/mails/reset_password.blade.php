@extends('mails.base')
@section('greeting', 'Hello')
@section('content')
    <p>You are receiving this email because we received a password reset request for your account</p>
    <br>
    <a class="button" href="{{ $data['link'] }}">
        Reset Password
    </a>
    <br>
    <br>
    <p>
        This password reset link will expire in 60 minutes. <br>
        If you did not request a password reset, no further action is required
    </p>
    <hr>
    <br>
    <span style="font-size: 13px">
        If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:
        <a href="{{ $data['link'] }}">{{ $data['link'] }}</a>
    </span>
@endsection
