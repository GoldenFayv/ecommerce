@extends('mails.base')
@section('content')
    <p style="margin-bottom: 10px;">Hi {{ $data['name'] }}</p>
    <p style="margin-bottom: 10px;">
        We are pleased to have you as a member of NKM Family.
        <br>
        Your Account has been successfully created, Just One Step to Go.
        Use the code below to verify your email
    </p>
    <b style="margin-bottom: 10px;color: #6576ff">{{ $data['code'] }}</b>
    <br>
    <span>Note: This Codes Expires after 10 Minutes</span>
@endsection
