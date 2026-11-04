@extends('layouts.base')

@section('body')
    <div class="min-h-screen bg-white">
        {{ $slot }}
    </div>
@endsection
