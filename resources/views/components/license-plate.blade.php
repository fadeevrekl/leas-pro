@props(['plate'])

@php
    $cleanPlate = preg_replace('/\s+/', '', $plate);
    preg_match('/^([А-ЯA-Z])(\d{3})([А-ЯA-Z]{2})(\d{2,3})$/', $cleanPlate, $matches);
@endphp

<div {{ $attributes->merge(['class' => 'license-plate-v2']) }}>
    @if(count($matches) === 5)
        <span class="letters">{{ $matches[1] }}</span>
        <span class="numbers">{{ $matches[2] }}</span>
        <span class="letters">{{ $matches[3] }}</span>
        <div class="region">
            {{ $matches[4] }}<span class="rus">rus</span>
        </div>
    @else
        <span class="numbers">{{ $plate }}</span>
    @endif
</div>