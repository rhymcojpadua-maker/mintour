@props(['slug'])
@php
    $paths = [
        'beaches' => '<path d="M3 18c2.2-1.8 4.3-1.8 6.5 0s4.3 1.8 6.5 0 4.3-1.8 6.5 0"/><path d="M3 13c2.2-1.8 4.3-1.8 6.5 0s4.3 1.8 6.5 0 4.3-1.8 6.5 0"/><path d="M12 3v5m-2-3 2 2 2-2"/>',
        'nature' => '<path d="M12 21V11"/><path d="M12 14c-5 0-8-3-8-8 5 0 8 3 8 8Z"/><path d="M12 11c0-4 2.5-7 7-8 0 4.5-3 8-7 8Z"/><path d="M7 21h10"/>',
        'culture' => '<path d="M3 21h18M5 21V9l7-5 7 5v12"/><path d="M3 9h18M9 21v-6h6v6"/><path d="M12 4V2"/>',
        'food' => '<path d="M7 3v7M4 3v4a3 3 0 0 0 6 0V3M7 10v11"/><path d="M16 3v18M16 3c3 2 4 5 4 9h-4"/>',
        'adventure' => '<path d="m3 20 7-12 4 6 3-4 4 10H3Z"/><path d="m8 20 3-5 3 5"/><path d="m10 8 2-4 2 4"/>',
    ];
@endphp

<svg {{ $attributes->merge(['viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round']) }}
     aria-hidden="true">
    {!! $paths[$slug] ?? '<path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>' !!}
</svg>
