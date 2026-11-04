@extends('layouts.app')
@section('title','Local Products')
@section('content')
<div class="mx-auto max-w-7xl px-6 py-14"><h1 class="text-4xl font-bold">Local Products</h1><div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
@forelse($products as $item)<article class="overflow-hidden rounded-3xl bg-white shadow-card">@if($item->cover_image)<img src="{{ \App\Support\MediaUrl::resolve($item->cover_image) }}" class="h-52 w-full object-cover">@endif<div class="p-6"><h2 class="text-xl font-bold">{{ $item->name }}</h2><p class="mt-2 text-slate-600">{{ Str::limit($item->description,130) }}</p></div></article>@empty<p class="text-slate-500">No published entries yet.</p>@endforelse</div><div class="mt-8">{{ $products->links() }}</div></div>
@endsection
