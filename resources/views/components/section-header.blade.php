@props(['title', 'link' => null, 'linkText' => null])

<div {{ $attributes->merge(['class' => 'section-header']) }}>
    <div style="display:flex;align-items:center;gap:10px;">
        <span class="text-md font-semibold" style="letter-spacing:-0.01em;">{{ $title }}</span>
        {{ $slot }}
    </div>
    @if($link)
        <a href="{{ $link }}" class="text-sm font-medium" style="color:var(--blue);text-decoration:none;">
            {{ $linkText ?? 'Voir tout →' }}
        </a>
    @endif
</div>
