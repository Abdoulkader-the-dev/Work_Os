@props(['label', 'value', 'subvalue' => null, 'delta' => null, 'deltaColor' => null])

<div {{ $attributes->merge(['class' => 'bento-card kpi-card']) }} style="padding:20px;">
    <div class="text-sm font-medium text-2" style="margin-bottom:18px;">{{ $label }}</div>
    <div class="text-2xl font-semibold text-1" style="letter-spacing:-0.04em;line-height:1;">{{ $value }}</div>
    @if($subvalue)
        <div class="text-xs text-3 f-mono" style="margin-top:6px;">
            {{ $subvalue }}
        </div>
    @endif
    @if($delta !== null)
        <div class="text-xs f-mono" style="margin-top:6px; color: {{ $deltaColor ?? 'var(--text-3)' }}">
            {{ $delta }}
        </div>
    @endif
</div>
