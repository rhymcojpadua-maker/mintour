@extends('layouts.app')
@section('title',$product->name)
@section('content')<div class="mx-auto max-w-5xl px-6 py-14"><a href="{{ route('products.index') }}" class="text-teal-700">← Back</a><h1 class="mt-4 text-5xl font-bold">{{ $product->name }}</h1><p class="mt-2 text-slate-600">{{ $product->maker }}</p><p class="mt-6 text-2xl font-bold">₱{{ number_format($product->price,2) }}</p><p class="mt-4 text-slate-600">{{ $product->description }}</p></div>@endsection
