{{-- resources/views/livewire/items/item-panel.blade.php --}}

{{-- Div racine fixed — ne prend aucun espace dans le flux --}}
<div x-data style="position:fixed;inset:0;pointer-events:none;z-index:40;">

    {{-- Overlay --}}
    <div x-cloak
         x-show="$wire.isOpen"
         x-transition.opacity.duration.180ms
         style="position:absolute;inset:0;background:rgba(0,0,0,0.2);backdrop-filter:blur(2px);pointer-events:auto;"
         wire:click="closePanel()">
    </div>

    {{-- Panel --}}
    <div x-cloak
         x-show="$wire.isOpen"
         x-transition.opacity.duration.180ms
         class="item-panel"
         style="position:absolute;right:0;top:0;height:100%;width:520px;background:white;border-left:1px solid var(--border);display:flex;flex-direction:column;box-shadow:-16px 0 48px rgba(0,0,0,0.08);pointer-events:auto;">

        @if($item)

        {{-- ── HEADER ── --}}
        <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);flex-shrink:0;">
            <div style="display:flex;align-items:flex-start;gap:12px;">

                {{-- Checkbox done --}}
                <div style="margin-top:3px;cursor:pointer;flex-shrink:0;"
                     wire:click="saveField('status', '{{ $item->status === 'done' ? 'todo' : 'done' }}')">
                    <div style="width:18px;height:18px;border-radius:5px;border:1.5px solid {{ $item->status === 'done' ? '#16a34a' : 'var(--border-md)' }};background:{{ $item->status === 'done' ? '#dcfce7' : 'transparent' }};display:flex;align-items:center;justify-content:center;transition:all .15s;">
                        @if($item->status === 'done')
                            <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                <path d="M2 5l2.5 2.5L8 3" stroke="#16a34a" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        @endif
                    </div>
                </div>

                {{-- Titre --}}
                <input type="text"
                       value="{{ $item->name }}"
                       style="flex:1;font-size:18px;font-weight:600;letter-spacing:-0.02em;background:transparent;border:none;border-bottom:2px solid transparent;outline:none;font-family:'DM Sans',sans-serif;color:var(--text-1);transition:border-color .15s;padding:0 0 2px;"
                       onfocus="this.style.borderColor='var(--blue)'" onblur="this.style.borderColor='transparent'"
                       wire:blur="saveField('name', $event.target.value)">

                {{-- Badge obstacles --}}
                @if($item->obstacles)
                    <span style="font-size:10px;font-weight:500;padding:2px 8px;border-radius:20px;background:#fff4e5;color:#ea580c;white-space:nowrap;flex-shrink:0;margin-top:3px;">
                        ⚠ Bloqué
                    </span>
                @endif

                {{-- Fermer --}}
                <button wire:click="closePanel()"
                        style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;border-radius:6px;border:none;background:none;color:var(--text-3);cursor:pointer;font-size:18px;flex-shrink:0;transition:background .15s;"
                        onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''">
                    ✕
                </button>
            </div>

            {{-- Breadcrumb --}}
            <div style="display:flex;align-items:center;gap:4px;margin-top:8px;margin-left:30px;">
                <a href="{{ route('boards.show', $item->group->board) }}"
                   style="font-size:11px;color:var(--blue);text-decoration:none;font-family:'DM Mono',monospace;">
                    {{ $item->group->board->name ?? '' }}
                </a>
                <span style="color:var(--text-3);font-size:11px;">/</span>
                <span style="font-size:11px;color:var(--text-3);font-family:'DM Mono',monospace;">
                    {{ $item->group->name ?? '' }}
                </span>
            </div>
        </div>

        {{-- ── TABS ── --}}
        <div style="display:flex;padding:0 24px;border-bottom:1px solid var(--border);flex-shrink:0;">
            @foreach(['details' => 'Détails', 'comments' => 'Commentaires', 'history' => 'Historique'] as $tab => $label)
                <button wire:click="$set('activeTab', '{{ $tab }}')"
                        style="padding:12px 0;margin-right:24px;font-size:13px;font-weight:500;background:none;border:none;border-bottom:2px solid {{ $activeTab === $tab ? 'var(--text-1)' : 'transparent' }};color:{{ $activeTab === $tab ? 'var(--text-1)' : 'var(--text-2)' }};cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .15s;white-space:nowrap;">
                    {{ $label }}
                    @if($tab === 'comments' && $item->comments->count() > 0)
                        <span style="font-size:10px;font-family:'DM Mono',monospace;background:var(--bg);padding:1px 5px;border-radius:10px;margin-left:4px;">{{ $item->comments->count() }}</span>
                    @endif
                </button>
            @endforeach
        </div>

        {{-- ── CONTENT ── --}}
        <div style="flex:1;overflow-y:auto;padding:0 24px;">

            {{-- ─ TAB DÉTAILS ─ --}}
            @if($activeTab === 'details')
                <div style="padding:8px 0;">

                    @php
                        $row   = 'display:flex;align-items:flex-start;gap:16px;padding:12px 0;border-bottom:1px solid var(--border);';
                        $label = 'width:120px;font-size:13px;color:var(--text-2);flex-shrink:0;padding-top:2px;';
                        $val   = 'flex:1;';
                    @endphp

                    {{-- Statut --}}
                    <div style="{{ $row }}">
                        <span style="{{ $label }}">Statut</span>
                        <div style="{{ $val }}" x-data="{ open: false }">
                            <button @click="open = !open" @click.outside="open = false"
                                    style="display:flex;align-items:center;gap:6px;padding:4px 10px;border-radius:20px;border:none;cursor:pointer;font-size:12px;font-weight:500;font-family:'DM Sans',sans-serif;"
                                    class="s-{{ $item->status }}">
                                <span style="width:6px;height:6px;border-radius:50%;background:currentColor;opacity:.7;"></span>
                                {{ match($item->status) { 'done'=>'Achevé','progress'=>'En cours','todo'=>'Non commencé','blocked'=>'Bloqué','ongoing'=>'Continu',default=>ucfirst($item->status) } }}
                            </button>
                            <div x-show="open" style="margin-top:4px;background:white;border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.1);padding:4px;position:relative;z-index:10;">
                                @foreach(['done'=>'Achevé','progress'=>'En cours','todo'=>'Non commencé','blocked'=>'Bloqué','ongoing'=>'Continu'] as $val2=>$lbl)
                                    <div style="display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:6px;font-size:12px;cursor:pointer;transition:background .12s;"
                                         onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''"
                                         wire:click="saveField('status','{{ $val2 }}')" @click="open=false">
                                        <span class="s-{{ $val2 }}" style="width:8px;height:8px;border-radius:50%;display:inline-block;"></span>
                                        {{ $lbl }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Priorité --}}
                    <div style="{{ $row }}">
                        <span style="{{ $label }}">Priorité</span>
                        <div style="flex:1;" x-data="{ open: false }">
                            <button @click="open = !open" @click.outside="open = false"
                                    style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:500;font-family:'DM Sans',sans-serif;background:none;border:none;cursor:pointer;color:var(--text-1);">
                                <span style="width:8px;height:8px;border-radius:50%;background:{{ match($item->priority) {'critique'=>'#8b5cf6','haute'=>'#ef4444','moyenne'=>'#FFD100','basse'=>'#22c55e',default=>'#a3a39f'} }};"></span>
                                {{ ucfirst($item->priority ?? 'Moyenne') }}
                            </button>
                            <div x-show="open" style="margin-top:4px;background:white;border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.1);padding:4px;position:relative;z-index:10;">
                                @foreach(['critique'=>['#8b5cf6','Critique'],'haute'=>['#ef4444','Haute'],'moyenne'=>['#FFD100','Moyenne'],'basse'=>['#22c55e','Basse']] as $v=>[$color,$lbl])
                                    <div style="display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:6px;font-size:12px;cursor:pointer;transition:background .12s;"
                                         onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''"
                                         wire:click="saveField('priority','{{ $v }}')" @click="open=false">
                                        <span style="width:7px;height:7px;border-radius:50%;background:{{ $color }};"></span>{{ $lbl }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Assignés --}}
                    <div style="{{ $row }}">
                        <span style="{{ $label }}">Assigné(s)</span>
                        <div style="flex:1;" x-data="{ addOpen: false }">
                            <div style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;">
                                @foreach($item->assignees as $assignee)
                                    <div style="display:flex;align-items:center;gap:6px;padding:4px 8px;background:var(--bg);border-radius:20px;font-size:12px;">
                                        <div style="width:20px;height:20px;border-radius:50%;background:var(--text-1);color:#fff;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:600;">
                                            {{ strtoupper(substr($assignee->name, 0, 2)) }}
                                        </div>
                                        {{ $assignee->name }}
                                        <button wire:click="removeAssignee({{ $assignee->id }})"
                                                style="background:none;border:none;color:var(--text-3);cursor:pointer;font-size:12px;padding:0;line-height:1;">✕</button>
                                    </div>
                                @endforeach
                                <button @click="addOpen = !addOpen" @click.outside="addOpen = false"
                                        style="width:26px;height:26px;border-radius:50%;border:1.5px dashed var(--border-md);background:none;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-3);transition:all .15s;"
                                        onmouseover="this.style.borderColor='var(--blue)';this.style.color='var(--blue)'" onmouseout="this.style.borderColor='var(--border-md)';this.style.color='var(--text-3)'">
                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M6 2v8M2 6h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                </button>
                            </div>
                            <div x-show="addOpen" style="display:none;margin-top:8px;background:white;border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.1);padding:8px;">
                                <input type="text" wire:model.live="searchAssignee" placeholder="Rechercher..."
                                       style="width:100%;font-size:13px;padding:6px 10px;border-radius:6px;border:1px solid var(--border);outline:none;font-family:'DM Sans',sans-serif;"
                                       onfocus="this.style.borderColor='var(--blue)'" onblur="this.style.borderColor='var(--border)'">
                                <div style="margin-top:6px;max-height:160px;overflow-y:auto;">
                                    @foreach($this->availableUsers as $user)
                                        <div style="display:flex;align-items:center;gap:8px;padding:7px 8px;border-radius:6px;cursor:pointer;font-size:13px;transition:background .12s;"
                                             onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''"
                                             wire:click="addAssignee({{ $user->id }})" @click="addOpen=false">
                                            <div style="width:26px;height:26px;border-radius:50%;background:var(--text-1);color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;flex-shrink:0;">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </div>
                                            {{ $user->name }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Deadline --}}
                    <div style="{{ $row }}">
                        <span style="{{ $label }}">Deadline</span>
                        <div style="flex:1;">
                            <input type="text"
                                   value="{{ $item->deadline?->format('d M Y') ?? '' }}"
                                   placeholder="Choisir une date..."
                                   style="font-size:13px;background:none;border:none;border-bottom:1px solid var(--border);outline:none;padding:2px 0;font-family:'DM Mono',monospace;color:{{ $item->deadline?->isPast() ? '#dc2626' : 'var(--text-1)' }};cursor:pointer;width:100%;"
                                   x-init="flatpickr($el, { locale:'fr', dateFormat:'d M Y', onChange(dates){ $wire.saveField('deadline', dates[0]?.toISOString().split('T')[0] || '') } })">
                        </div>
                    </div>

                    {{-- Description --}}
                    <div style="{{ $row }}">
                        <span style="{{ $label }}">Description</span>
                        <div style="flex:1;" wire:key="item-description-{{ $item->id }}">
                            <div wire:ignore
                                 x-data
                                 x-init="
                                    const input = $refs.input;
                                    const editor = $refs.editor;
                                    editor.editor?.loadHTML(input.value || '');
                                    editor.addEventListener('trix-change', () => { input.value = editor.value; });
                                    editor.addEventListener('trix-blur', () => { $wire.saveField('description', input.value); });
                                 ">
                                <input id="item-description-input-{{ $item->id }}" type="hidden" x-ref="input" value="{{ $item->description ?? '' }}">
                                <trix-editor input="item-description-input-{{ $item->id }}"
                                             x-ref="editor"
                                             class="trix-content"
                                             style="background:var(--bg);border:1px solid var(--border);border-radius:10px;min-height:160px;padding:10px 12px;"></trix-editor>
                            </div>
                        </div>
                    </div>

                    {{-- Livrable --}}
                    <div style="{{ $row }}">
                        <span style="{{ $label }}">Livrable</span>
                        <div style="flex:1;">
                            <textarea rows="2"
                                      placeholder="Ex: Draft MoU envoyé..."
                                      style="width:100%;font-size:13px;font-family:'DM Sans',sans-serif;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:8px 10px;resize:none;outline:none;color:var(--text-1);transition:border-color .15s;"
                                      onfocus="this.style.borderColor='var(--blue)'" onblur="this.style.borderColor='var(--border)'"
                                      wire:blur="saveField('deliverable', $event.target.value)">{{ $item->deliverable }}</textarea>
                        </div>
                    </div>

                    {{-- Journal obstacles --}}
                    <div style="display:flex;align-items:flex-start;gap:16px;padding:12px 0;">
                        <span style="width:120px;font-size:13px;color:var(--text-2);flex-shrink:0;padding-top:2px;">Obstacles</span>
                        <div style="flex:1;">
                            <textarea rows="3"
                                      placeholder="Décris les blocages rencontrés..."
                                      style="width:100%;font-size:13px;font-family:'DM Sans',sans-serif;background:{{ $item->obstacles ? '#fff4e5' : 'var(--bg)' }};border:1px solid {{ $item->obstacles ? '#fdba74' : 'var(--border)' }};border-radius:8px;padding:8px 10px;resize:none;outline:none;color:var(--text-1);transition:all .15s;"
                                      onfocus="this.style.borderColor='var(--blue)'" onblur="this.style.borderColor='{{ $item->obstacles ? '#fdba74' : 'var(--border)' }}'"
                                      wire:blur="saveField('obstacles', $event.target.value)">{{ $item->obstacles }}</textarea>
                            @if($item->obstacles)
                                <p style="font-size:11px;color:#ea580c;margin-top:4px;">⚠ Ce blocage sera signalé aux responsables</p>
                            @endif
                        </div>
                    </div>

                </div>
            @endif

            {{-- ─ TAB COMMENTAIRES ─ --}}
            @if($activeTab === 'comments')
                <div style="padding:16px 0;display:flex;flex-direction:column;gap:12px;">
                    @forelse($item->comments as $comment)
                        <div style="display:flex;gap:10px;align-items:flex-start;">
                            <div style="width:32px;height:32px;border-radius:50%;background:var(--text-1);color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;">
                                {{ strtoupper(substr($comment->user->name, 0, 2)) }}
                            </div>
                            <div style="flex:1;">
                                <div style="display:flex;align-items:baseline;gap:8px;margin-bottom:4px;">
                                    <span style="font-size:13px;font-weight:600;">{{ $comment->user->name }}</span>
                                    <span style="font-size:11px;color:var(--text-3);font-family:'DM Mono',monospace;">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <div style="background:var(--bg);border-radius:12px;border-top-left-radius:4px;padding:10px 14px;font-size:13px;line-height:1.5;">
                                    {!! $comment->body !!}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center;padding:32px;color:var(--text-3);">
                            <div style="font-size:24px;margin-bottom:8px;">💬</div>
                            <div style="font-size:13px;">Aucun commentaire</div>
                        </div>
                    @endforelse
                </div>
            @endif

            {{-- ─ TAB HISTORIQUE ─ --}}
            @if($activeTab === 'history')
                <div style="padding:16px 0;text-align:center;color:var(--text-3);font-size:13px;">
                    <div style="font-size:24px;margin-bottom:8px;">📋</div>
                    Historique bientôt disponible
                </div>
            @endif

        </div>

        {{-- ── ZONE COMMENTAIRE ── --}}
        @if($activeTab === 'comments')
            <div style="padding:14px 24px;border-top:1px solid var(--border);flex-shrink:0;">
                <div style="display:flex;gap:10px;align-items:flex-end;">
                    <div style="width:32px;height:32px;border-radius:50%;background:var(--text-1);color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 2)) }}
                    </div>
                    <div style="flex:1;" wire:key="item-comment-editor-{{ $item->id }}">
                        <div wire:ignore
                             x-data
                             x-init="
                                const input = $refs.input;
                                const editor = $refs.editor;
                                editor.editor?.loadHTML(input.value || '');
                                editor.addEventListener('trix-change', () => { input.value = editor.value; $wire.set('newComment', input.value); });
                                window.addEventListener('trix-clear-comment', () => {
                                    input.value = '';
                                    editor.editor?.loadHTML('');
                                });
                             ">
                            <input id="new-comment-input-{{ $item->id }}" type="hidden" x-ref="input" value="">
                            <trix-editor input="new-comment-input-{{ $item->id }}"
                                         x-ref="editor"
                                         class="trix-content"
                                         placeholder="Écrire un commentaire..."
                                         style="border:1px solid var(--border);border-radius:10px;min-height:110px;background:white;padding:8px 10px;"></trix-editor>
                        </div>
                    </div>
                    <button wire:click="addComment()"
                            style="width:36px;height:36px;background:var(--text-1);border:none;border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;transition:background .15s;"
                            onmouseover="this.style.background='#2a2a28'" onmouseout="this.style.background='var(--text-1)'">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M14 2L8 14l-2-5L1 5l13-3z" fill="white"/>
                        </svg>
                    </button>
                </div>
                <p style="font-size:11px;color:var(--text-3);margin-top:6px;margin-left:42px;">Le commentaire prend en charge le texte riche via Trix.</p>
            </div>
        @endif

        @endif {{-- end if($item) --}}

        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
        @endpush
    </div>
</div>
