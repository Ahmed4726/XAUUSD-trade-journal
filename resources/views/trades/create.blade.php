@extends('layouts.app')

@section('title', 'Add Trade | XAUUSD Trading Journal')

@section('content')
    <div class="mb-8"><p class="text-sm font-medium text-amber-300">New XAUUSD position</p><h1 class="mt-1 text-3xl font-bold text-white">Add trade</h1></div>
    @include('trades._form', ['action' => route('trades.store'), 'method' => 'POST', 'submitLabel' => 'Save trade'])
@endsection
