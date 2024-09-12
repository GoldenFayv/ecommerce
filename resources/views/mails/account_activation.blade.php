@extends('mails.base')
@section('content')
    <p style="margin-bottom: 10px;">Hi {{ $data['username'] }}</p>
    <p style="margin-bottom: 10px;">You are receiving this email because we received an account activation request for your account</p>
    Use this code to verify your email
    <br>
    <b style="margin-bottom: 10px;color: #6576ff">{{ $data['code'] }}</b>
    <span>Note: This Codes Expires after 10 Minutes</span>
@endsection
