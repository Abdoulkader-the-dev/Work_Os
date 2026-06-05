{{-- resources/views/livewire/meetings/meeting-editor.blade.php --}}

@section('page-title', 'Réunions')

@section('topbar-action')
    <button wire:click="saveMeeting" class="btn-primary" wire:loading.attr="disabled" wire:target="saveMeeting">
        <span wire:loading.remove wire:target="saveMeeting">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path d="M2 7l3.5 3.5L12 3" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <span wire:loading wire:target="saveMeeting">⏳</span>
        Enregistrer le compte rendu
    </button>
@endsection

<div style="max-width:820px;margin:0 auto;display:flex;flex-direction:column;gap:32px;">

    {{-- ── FEEDBACK SAUVEGARDE ── --}}
    <div x-show="$wire.saved" x-transition
         style="position:fixed;bottom:24px;right:24px;z-index:100;display:flex;align-items:center;gap:10px;padding:12px 18px;background:var(--text-1);color:white;border-radius:10px;font-size:13px;font-weight:500;box-shadow:0 8px 24px rgba(0,0,0,0.15);">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M3 8l3.5 3.5L13 4" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        {{ $saveMessage }}
    </div>

    {{-- ── HEADER SÉANCE ── --}}
    <div class="bento-card" style="padding:24px;cursor:default;" @click.stop>

        {{-- Titre --}}
        <input type="text"
               wire:model.blur="title"
               placeholder="Titre de la séance..."
               class="text-2xl font-semibold text-1"
               style="width:100%;background:transparent;border:none;border-bottom:2px solid var(--border);outline:none;padding:0 0 8px;margin-bottom:20px;transition:border-color .15s;"
               onfocus="this.style.borderColor='var(--blue)'" onblur="this.style.borderColor='var(--border)'">

        <div class="flex-between flex-wrap" style="align-items:flex-end;gap:20px;">

            {{-- Date --}}
            <div style="display:flex;flex-direction:column;gap:5px;">
                <label class="text-xs text-3 font-medium" style="letter-spacing:.06em;text-transform:uppercase;">Date</label>
                <div class="flex-center" style="position:relative;justify-content:flex-start;">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="position:absolute;left:10px;color:var(--text-3);pointer-events:none;">
                        <rect x="1" y="2" width="12" height="11" rx="2" stroke="currentColor" stroke-width="1.3"/>
                        <path d="M4 1v2M10 1v2M1 6h12" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                    </svg>
                    <input type="text"
                           value="{{ \Carbon\Carbon::parse($date)->isoFormat('D MMMM YYYY') }}"
                           class="text-sm font-medium"
                           style="padding:8px 12px 8px 32px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font-family:'DM Sans',sans-serif;outline:none;cursor:pointer;min-width:180px;transition:border-color .15s;"
                           onfocus="this.style.borderColor='var(--blue)'" onblur="this.style.borderColor='var(--border)'"
                           x-init="flatpickr($el, { locale:'fr', dateFormat:'d F Y', defaultDate: '{{ $date }}', onChange(dates){ $wire.set('date', dates[0]?.toISOString().split('T')[0] || '') } })">
                </div>
            </div>

            {{-- Participants --}}
            <div style="flex:1;min-width:240px;display:flex;flex-direction:column;gap:5px;">
                <label class="text-xs text-3 font-medium" style="letter-spacing:.06em;text-transform:uppercase;">Participants</label>
                <div class="flex-center flex-wrap" style="gap:6px;justify-content:flex-start;padding:6px 10px;border:1px solid var(--border);border-radius:8px;background:var(--bg);min-height:38px;transition:border-color .15s;"
                     onfocusin="this.style.borderColor='var(--blue)'" onfocusout="this.style.borderColor='var(--border)'">
                    @foreach($attendees as $i => $attendee)
                        <span class="badge" style="background:white;border:1px solid var(--border);gap:5px;">
                            {{ $attendee }}
                            <button wire:click="removeAttendee({{ $i }})"
                                    class="text-xs text-3"
                                    style="background:none;border:none;cursor:pointer;padding:0;line-height:1;transition:color .12s;"
                                    onmouseover="this.style.color='#dc2626'" onmouseout="this.style.color=''">✕</button>
                        </span>
                    @endforeach
                    <input type="text"
                           wire:model="attendeeInput"
                           wire:keydown.enter.prevent="addAttendee()"
                           wire:keydown.comma.prevent="addAttendee()"
                           placeholder="{{ empty($attendees) ? 'Ajouter un participant...' : '+' }}"
                           class="text-sm"
                           style="border:none;background:none;outline:none;font-family:'DM Sans',sans-serif;min-width:120px;flex:1;">
                </div>
                <p class="text-xs text-3">Appuyer sur Entrée ou virgule pour ajouter</p>
            </div>

        </div>
    </div>

    {{-- ── SECTION 01 — POINTS ── --}}
    <div>
        <div class="flex-center" style="gap:10px;margin-bottom:16px;justify-content:flex-start;">
            <span class="text-xs text-3 font-medium f-mono">01</span>
            <h3 class="text-md font-semibold" style="letter-spacing:-0.015em;">Points</h3>
            <div style="flex:1;height:1px;background:var(--border);"></div>
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            @foreach($bilan as $i => $point)
                <div wire:key="bilan-{{ $i }}" class="flex-center" style="gap:10px;padding:4px 0;" x-data>
                    <div style="width:6px;height:6px;border-radius:50%;background:var(--blue);flex-shrink:0;margin-top:2px;"></div>
                    <input type="text"
                           wire:model.blur="bilan.{{ $i }}"
                           placeholder="Ajouter un point..."
                           class="text-sm text-1"
                           style="flex:1;background:transparent;border:none;border-bottom:1px solid transparent;outline:none;padding:4px 0;transition:border-color .15s;"
                           onfocus="this.style.borderColor='var(--blue)'" onblur="this.style.borderColor='transparent'">
                    <button wire:click="removeBilanPoint({{ $i }})"
                            class="icon-btn text-3"
                            style="width:20px;height:20px;border:none;background:none;font-size:14px;opacity:0;"
                            x-on:mouseenter="$el.style.opacity='1'" x-on:mouseleave="$el.style.opacity='0'"
                            title="Supprimer">✕</button>
                </div>
            @endforeach
        </div>

        <button wire:click="addBilanPoint" class="text-sm text-3 flex-center" style="gap:6px;margin-top:10px;background:none;border:none;cursor:pointer;padding:4px 8px;border-radius:6px;transition:all .15s;"
                onmouseover="this.style.color='var(--blue)';this.style.background='var(--blue-light)'" onmouseout="this.style.color='';this.style.background=''">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 2v10M2 7h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            Ajouter un point
        </button>
    </div>

    {{-- ── SECTION 02 — RECOMMANDATIONS ── --}}
    <div>
        <div class="flex-center" style="gap:10px;margin-bottom:16px;justify-content:flex-start;">
            <span class="text-xs text-3 font-medium f-mono">02</span>
            <h3 class="text-md font-semibold" style="letter-spacing:-0.015em;">Recommandations</h3>
            <div style="flex:1;height:1px;background:var(--border);"></div>
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            @foreach($recommendations as $i => $rec)
                <div wire:key="rec-{{ $i }}" class="flex-center" style="gap:10px;padding:4px 0;" x-data>
                    <div style="width:6px;height:6px;border-radius:3px;background:var(--yellow);flex-shrink:0;margin-top:2px;"></div>
                    <input type="text"
                           wire:model.blur="recommendations.{{ $i }}"
                           placeholder="Ajouter une recommandation..."
                           class="text-sm text-1"
                           style="flex:1;background:transparent;border:none;border-bottom:1px solid transparent;outline:none;padding:4px 0;transition:border-color .15s;"
                           onfocus="this.style.borderColor='var(--blue)'" onblur="this.style.borderColor='transparent'">
                    <button wire:click="removeRecommendation({{ $i }})"
                            class="icon-btn text-3"
                            style="width:20px;height:20px;border:none;background:none;font-size:14px;opacity:0;"
                            x-on:mouseenter="$el.style.opacity='1'" x-on:mouseleave="$el.style.opacity='0'">✕</button>
                </div>
            @endforeach
        </div>

        <button wire:click="addRecommendation" class="text-sm text-3 flex-center" style="gap:6px;margin-top:10px;background:none;border:none;cursor:pointer;padding:4px 8px;border-radius:6px;transition:all .15s;"
                onmouseover="this.style.color='var(--blue)';this.style.background='var(--blue-light)'" onmouseout="this.style.color='';this.style.background=''">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 2v10M2 7h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            Ajouter une recommandation
        </button>
    </div>

    {{-- ── SECTION 03 — ACTIONS ATTENDUES ── --}}
    <div>
        <div class="flex-center" style="gap:10px;margin-bottom:16px;justify-content:flex-start;">
            <span class="text-xs text-3 font-medium f-mono">03</span>
            <h3 class="text-md font-semibold" style="letter-spacing:-0.015em;">Actions attendues</h3>
            @php $pendingCount = collect($actions)->where('converted', false)->where('text', '!=', '')->count(); @endphp
            @if($pendingCount > 0)
                <span class="badge s-ongoing f-mono" style="font-size:10px;">
                    {{ $pendingCount }} en attente
                </span>
            @endif
            <div style="flex:1;height:1px;background:var(--border);"></div>
        </div>

        <div style="display:flex;flex-direction:column;gap:10px;">
            @foreach($actions as $i => $action)
                <div wire:key="action-{{ $i }}"
                     class="bento-card"
                     style="padding:16px 18px;cursor:default;{{ ($action['converted'] ?? false) ? 'border-color:#bbf7d0;background:#f0fdf4;' : '' }}"
                     @click.stop>

                    <div class="flex-between" style="align-items:flex-start;gap:10px;margin-bottom:12px;">

                        {{-- Indicateur --}}
                        <div style="width:6px;height:6px;border-radius:50%;margin-top:7px;flex-shrink:0;background:{{ ($action['converted'] ?? false) ? '#16a34a' : 'var(--blue)' }};"></div>

                        {{-- Texte de l'action --}}
                        <input type="text"
                               wire:model.blur="actions.{{ $i }}.text"
                               placeholder="Décrire l'action attendue..."
                               {{ ($action['converted'] ?? false) ? 'disabled' : '' }}
                               class="text-sm font-medium text-1"
                               style="flex:1;background:transparent;border:none;border-bottom:1px solid {{ ($action['converted'] ?? false) ? 'transparent' : 'var(--border)' }};outline:none;padding:4px 0;transition:border-color .15s;{{ ($action['converted'] ?? false) ? 'text-decoration:line-through;color:var(--text-3);' : '' }}"
                               onfocus="this.style.borderColor='var(--blue)'" onblur="this.style.borderColor='var(--border)'">

                        {{-- Supprimer --}}
                        @if(!($action['converted'] ?? false))
                            <button wire:click="removeAction({{ $i }})"
                                    class="icon-btn text-3"
                                    style="width:22px;height:22px;border:none;background:none;font-size:14px;flex-shrink:0;transition:all .15s;"
                                    onmouseover="this.style.color='#dc2626'" onmouseout="this.style.color=''"
                                    title="Supprimer">✕</button>
                        @endif
                    </div>

                    <div class="flex-center flex-wrap" style="gap:12px;padding-left:16px;justify-content:flex-start;">

                        {{-- Assigné --}}
                        <div class="flex-center" style="gap:6px;">
                            <svg width="13" height="13" viewBox="0 0 13 13" fill="none" style="color:var(--text-3);"><circle cx="6.5" cy="4.5" r="2.5" stroke="currentColor" stroke-width="1.3"/><path d="M1.5 12c0-2.76 2.24-5 5-5s5 2.24 5 5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
                            <select wire:model="actions.{{ $i }}.assignee_id"
                                    {{ ($action['converted'] ?? false) ? 'disabled' : '' }}
                                    class="text-xs text-2"
                                    style="border:1px solid var(--border);border-radius:6px;padding:4px 8px;background:white;font-family:'DM Sans',sans-serif;outline:none;cursor:pointer;transition:border-color .15s;"
                                    onfocus="this.style.borderColor='var(--blue)'" onblur="this.style.borderColor='var(--border)'">
                                <option value="">Assigner à...</option>
                                @foreach($this->availableUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Deadline --}}
                        <div class="flex-center" style="gap:6px;">
                            <svg width="13" height="13" viewBox="0 0 13 13" fill="none" style="color:var(--text-3);"><rect x="1" y="2" width="11" height="10" rx="2" stroke="currentColor" stroke-width="1.3"/><path d="M4 1v2M9 1v2M1 5.5h11" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
                            <input type="text"
                                   value="{{ $action['deadline'] ? \Carbon\Carbon::parse($action['deadline'])->isoFormat('D MMM') : '' }}"
                                   placeholder="Deadline"
                                   {{ ($action['converted'] ?? false) ? 'disabled' : '' }}
                                   class="text-xs f-mono text-2"
                                   style="border:1px solid var(--border);border-radius:6px;padding:4px 8px;background:white;outline:none;cursor:pointer;width:110px;transition:border-color .15s;"
                                   onfocus="this.style.borderColor='var(--blue)'" onblur="this.style.borderColor='var(--border)'"
                                   x-init="flatpickr($el, { locale:'fr', dateFormat:'d M', onChange(dates){ $wire.set('actions.{{ $i }}.deadline', dates[0]?.toISOString().split('T')[0] || '') } })">
                        </div>

                        {{-- Spacer --}}
                        <div style="flex:1;"></div>

                        {{-- Bouton convertir / état converti --}}
                        @if($action['converted'] ?? false)
                            <div class="flex-center text-xs font-medium" style="gap:6px;color:#16a34a;">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <path d="M2 7l3.5 3.5L12 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Tâche créée
                                @if($action['item_id'] ?? false)
                                    <span style="color:var(--blue);cursor:pointer;font-weight:400;text-decoration:underline;"
                                          wire:click="$dispatch('open-item-panel', { itemId: {{ $action['item_id'] }} })">
                                        → Voir la tâche
                                    </span>
                                @endif
                            </div>
                        @else
                            <button wire:click="convertActionToTask({{ $i }})"
                                    wire:loading.attr="disabled"
                                    wire:target="convertActionToTask({{ $i }})"
                                    class="text-xs font-medium flex-center"
                                    style="gap:6px;font-family:'DM Sans',sans-serif;padding:6px 12px;border-radius:6px;border:1px solid rgba(0,145,205,0.3);color:var(--blue);background:none;cursor:pointer;transition:all .18s;white-space:nowrap;"
                                    onmouseover="this.style.background='var(--blue-light)';this.style.borderColor='var(--blue)'" onmouseout="this.style.background='none';this.style.borderColor='rgba(0,145,205,0.3)'">
                                <span wire:loading.remove wire:target="convertActionToTask({{ $i }})">
                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" style="flex-shrink:0;"><path d="M2 6h7M7 4l2 2-2 2" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                                <span wire:loading wire:target="convertActionToTask({{ $i }})">⏳</span>
                                Convertir en tâche
                            </button>
                        @endif

                    </div>
                </div>
            @endforeach
        </div>

        <button wire:click="addAction" class="text-sm text-3 flex-center" style="gap:6px;margin-top:12px;background:none;border:none;cursor:pointer;padding:4px 8px;border-radius:6px;transition:all .15s;"
                onmouseover="this.style.color='var(--blue)';this.style.background='var(--blue-light)'" onmouseout="this.style.color='';this.style.background=''">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 2v10M2 7h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            Ajouter une action
        </button>
    </div>

    {{-- ── FOOTER ── --}}
    <div class="flex-between" style="padding-top:20px;border-top:1px solid var(--border);padding-bottom:40px;">
        <div class="flex-center text-xs text-3" style="gap:8px;">
            <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><circle cx="6.5" cy="6.5" r="5.5" stroke="currentColor" stroke-width="1.2"/><path d="M6.5 4v3l2 1.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
            Sauvegarde automatique activée
        </div>
        <div class="flex-center" style="gap:10px;">
            <button class="view-btn text-sm font-medium" style="padding:9px 18px;height:auto;border:1px solid var(--border);">
                Aperçu PDF
            </button>
            <button wire:click="saveMeeting" class="btn-primary"
                    wire:loading.attr="disabled" wire:target="saveMeeting">
                <span wire:loading.remove wire:target="saveMeeting">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7l3.5 3.5L12 3" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <span wire:loading wire:target="saveMeeting">⏳</span>
                Enregistrer le compte rendu
            </button>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
@endpush
