@extends('layouts.app')
@section('title',$restaurant->name)
@section('content')<div class="mx-auto max-w-5xl px-6 py-14"><a href="{{ route('restaurants.index') }}" class="text-teal-700">← Back</a><h1 class="mt-4 text-5xl font-bold">{{ $restaurant->name }}</h1><p class="mt-2 text-teal-700">{{ $restaurant->cuisine }}</p><p class="mt-6 text-slate-600">{{ $restaurant->description }}</p><p class="mt-4">{{ $restaurant->address }}</p></div>@endsection
