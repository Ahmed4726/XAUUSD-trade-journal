@extends('layouts.app')
@section('title', 'Edit Trade Mistake | XAUUSD Trading Journal')
@section('content')
    <div class="mb-8"><p class="text-sm font-medium text-amber-300">Execution review</p><h1 class="mt-1 text-3xl font-bold text-white">Edit mistake</h1></div>
    @include('trade-mistakes._form', ['action' => route('trade-mistakes.update', $tradeMistake), 'method' => 'PUT', 'submitLabel' => 'Update mistake'])
@endsection
