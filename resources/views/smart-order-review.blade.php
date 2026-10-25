@extends('layouts.smart-order')

@section('title', 'Preparando confirmación | SIVIA')

@section('content')
<form id="smart-order-confirmation-token" method="POST" action="{{ route('smart.order.confirm') }}" class="hidden" aria-hidden="true">
    @csrf
    <input type="hidden" name="confirmation_token" value="{{ $confirmationToken }}">
</form>
@endsection
