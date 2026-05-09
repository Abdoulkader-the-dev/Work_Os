<x-app-layout>
@section('page-title', 'Membres')
<div style="display:flex;flex-direction:column;gap:10px;">
    @foreach(\App\Models\User::all() as $member)
        <div class="bento-card" style="padding:16px 20px;display:flex;align-items:center;gap:14px;">
            <div style="width:40px;height:40px;border-radius:50%;background:var(--color-text-1);color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:600;flex-shrink:0;">
                {{ strtoupper(substr($member->name, 0, 2)) }}
            </div>
            <div style="flex:1;">
                <div style="font-size:14px;font-weight:500;">{{ $member->name }}</div>
                <div style="font-size:12px;color:var(--color-text-3);margin-top:2px;">{{ $member->email }}</div>
            </div>
            <span style="font-size:11px;font-family:'DM Mono',monospace;padding:3px 10px;background:var(--color-bg);border-radius:20px;color:var(--color-text-2);">
                {{ $member->items()->count() }} tâches
            </span>
        </div>
    @endforeach
</div>
</x-app-layout>