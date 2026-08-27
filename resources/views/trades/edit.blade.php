@extends('layouts.app')

@section('title', 'Edit Trade | XAUUSD Trading Journal')

@section('content')
    <div class="mb-8"><p class="text-sm font-medium text-amber-300">{{ $trade->symbol }} · {{ $trade->trade_date->format('d M Y') }}</p><h1 class="mt-1 text-3xl font-bold text-white">Edit trade</h1></div>
    @include('trades._form', ['action' => route('trades.update', $trade), 'method' => 'PUT', 'submitLabel' => 'Update trade'])
@endsection
