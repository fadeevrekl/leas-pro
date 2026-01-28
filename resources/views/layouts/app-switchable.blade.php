{{-- resources/views/layouts/app-switchable.blade.php --}}
@php
    // Простой переключатель тем через GET параметр
    if (request()->has('theme')) {
        $theme = request()->get('theme');
        session(['theme' => $theme]);
    } else {
        $theme = session('theme', 'tabler'); // По умолчанию Tabler
    }
@endphp

@if($theme === 'tabler')
    @extends('layouts.app-tabler')
@else
    @extends('layouts.app-bootstrap')
@endif