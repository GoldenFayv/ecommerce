@extends('mails.base')
@section('greeting')
Hi {{ $data["username"] }}
@endsection
@section('content')
<p>You are receiving this email because we received an account deletion request from your account</p>
    <br>
    Use this code to verify this action
    <br>
    <br>
    <b class="code">{{ $data['code'] }}</b>
    <br>
    <br>
    <span>Note: This Codes Expires after 10 Minutes</span>
@endsection
