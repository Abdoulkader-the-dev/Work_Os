@props([
    'title',
    'description' => null,
    'compact' => false
])

<div {{ $attributes->merge(['class' => 'empty-state' . ($compact ? ' empty-state--compact' : '')]) }}>
    @if(isset($icon))
        <div class="empty-state__icon">
            {{ $icon }}
        </div>
    @endif
    
    <div class="empty-state__title">{{ $title }}</div>
    
    @if($description)
        <div class="empty-state__copy" style="max-width: 400px; margin: 0 auto;">{{ $description }}</div>
    @endif
    
    @if(isset($slot) && $slot->isNotEmpty())
        <div style="margin-top: 24px;">
            {{ $slot }}
        </div>
    @endif
</div>
