@props(['status', 'label' => null])

@php
    $statusClasses = [
        'done'     => 's-done',
        'progress' => 's-progress',
        'todo'     => 's-todo',
        'blocked'  => 's-blocked',
        'ongoing'  => 's-ongoing',
    ];

    $labels = [
        'done'     => 'Achevé',
        'progress' => 'En cours',
        'todo'     => 'Non commencé',
        'blocked'  => 'Bloqué',
        'ongoing'  => 'Continu',
    ];

    $class = $statusClasses[$status] ?? 's-todo';
    $displayLabel = $label ?? $labels[$status] ?? ucfirst($status);
@endphp

<span {{ $attributes->merge(['class' => "badge $class"]) }}>
    {{ $displayLabel }}
</span>
