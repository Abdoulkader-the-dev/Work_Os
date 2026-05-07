{{-- resources/views/livewire/items/item-panel.blade.php --}}

{{-- Overlay --}}
<div>
    <div x-show="$wire.isOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="position:fixed;inset:0;background:rgba(0,0,0,0.2);z-index:40;backdrop-filter:blur(2px);"
         wire:click="closePanel()">
    </div>

    {{-- Panel --}}
    <div x-show="$wire.isOpen"
         x-transition:enter="transition ease-out duration-220"
         x-transition:enter-start="translate-x-full opacity-0"
         x-transition:enter-end="translate-x-0 opacity-100"
         x-transition:leave="transition ease-in duration-180"
         x-transition:leave-start="translate-x-0 opacity-100"
         x-transition:leave-end="translate-x-full opacity-0"
         style="position:fixed;right:0;top:0;height:100%;width:520px;background:white;border-left:1px solid var(--border);z-index:50;display:flex;flex-direction:column;box-shadow:-16px 0 48px rgba(0,0,0,0.08);"
         @click.outside="$wire.closePanel()">

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
                        $fieldStyle = 'display:flex;align-items:flex-start;gap:16px;padding:12px 0;border-bottom:1px solid var(--border);';
                        $labelStyle = 'width:120px;font-size:13px;color:var(--text-2);flex-shrink:0;padding-top:2px;';
                        $valueStyle = 'flex:1;';
                    @endphp

                    {{-- Statut --}}
                    <div style="{{ $fieldStyle }}">
                        <span style="{{ $labelStyle }}">Statut</span>
                        <div style="{{ $valueStyle }}" x-data="{ open: false }">
                            <button @click="open = !open" @click.outside="open = false"
                                    style="display:flex;align-items:center;gap:6px;padding:4px 10px;border-radius:20px;border:none;cursor:pointer;font-size:12px;font-weight:500;font-family:'DM Sans',sans-serif;"
                                    class="s-{{ $item->status }}">
                                <span style="width:6px;height:6px;border-radius:50%;background:currentColor;opacity:.7;"></span>
                                {{ match($item->status) { 'done'=>'Achevé','progress'=>'En cours','todo'=>'Non commencé','blocked'=>'Bloqué','ongoing'=>'Continu',default=>ucfirst($item->status) } }}
                            </button>
                            <div x-show="open" x-transition
                                 style="margin-top:4px;background:white;border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.1);padding:4px;z-index:10;position:relative;">
                                @foreach(['done'=>'Achevé','progress'=>'En cours','todo'=>'Non commencé','blocked'=>'Bloqué','ongoing'=>'Continu'] as $val=>$label)
                                    <div style="display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:6px;font-size:12px;cursor:pointer;transition:background .12s;"
                                         onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''"
                                         wire:click="saveField('status','{{ $val }}')" @click="open=false">
                                        <span class="s-{{ $val }}" style="width:8px;height:8px;border-radius:50%;display:inline-block;"></span>
                                        {{ $label }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Priorité --}}
                    <div style="{{ $fieldStyle }}">
                        <span style="{{ $labelStyle }}">Priorité</span>
                        <div style="{{ $valueStyle }}" x-data="{ open: false }">
                            <button @click="open = !open" @click.outside="open = false"
                                    style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:500;font-family:'DM Sans',sans-serif;background:none;border:none;cursor:pointer;color:var(--text-1);">
                                <span style="width:8px;height:8px;border-radius:50%;background:{{ match($item->priority) {'critique'=>'#8b5cf6','haute'=>'#ef4444','moyenne'=>'#FFD100','basse'=>'#22c55e',default=>'#a3a39f'} }};"></span>
                                {{ ucfirst($item->priority ?? 'Moyenne') }}
                            </button>
                            <div x-show="open" x-transition
                                 style="margin-top:4px;background:white;border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.1);padding:4px;position:relative;z-index:10;">
                                @foreach(['critique'=>['#8b5cf6','Critique'],'haute'=>['#ef4444','Haute'],'moyenne'=>['#FFD100','Moyenne'],'basse'=>['#22c55e','Basse']] as $val=>[$color,$label])
                                    <div style="display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:6px;font-size:12px;cursor:pointer;transition:background .12s;"
                                         onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''"
                                         wire:click="saveField('priority','{{ $val }}')" @click="open=false">
                                        <span style="width:7px;height:7px;border-radius:50%;background:{{ $color }};"></span>{{ $label }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Assignés --}}
                    <div style="{{ $fieldStyle }}">
                        <span style="{{ $labelStyle }}">Assigné(s)</span>
                        <div style="{{ $valueStyle }}" x-data="{ addOpen: false }">
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
                            {{-- Assignee picker --}}
                            <div x-show="addOpen" x-transition
                                 style="margin-top:8px;background:white;border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.1);padding:8px;">
                                <input type="text" wire:model.live="searchAssignee" placeholder="Rechercher un membre..."
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
                                            <div>
                                                <div style="font-weight:500;">{{ $user->name }}</div>
                                                <div style="font-size:11px;color:var(--text-3);">{{ $user->role ?? 'Membre' }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Deadline --}}
                    <div style="{{ $fieldStyle }}">
                        <span style="{{ $labelStyle }}">Deadline</span>
                        <div style="{{ $valueStyle }}">
                            <input type="text"
                                   value="{{ $item->deadline?->format('d M Y') ?? '' }}"
                                   placeholder="Choisir une date..."
                                   style="font-size:13px;background:none;border:none;border-bottom:1px solid var(--border);outline:none;padding:2px 0;font-family:'DM Mono',monospace;color:{{ $item->deadline?->isPast() ? '#dc2626' : 'var(--text-1)' }};cursor:pointer;width:100%;"
                                   onfocus="this.style.borderColor='var(--blue)'" onblur="this.style.borderColor='var(--border)'"
                                   x-init="flatpickr($el, { locale:'fr', dateFormat:'d M Y', onChange(dates){ $wire.saveField('deadline', dates[0]?.toISOString().split('T')[0] || '') } })">
                        </div>
                    </div>

                    {{-- Livrable --}}
                    <div style="{{ $fieldStyle }}">
                        <span style="{{ $labelStyle }}">Livrable</span>
                        <div style="{{ $valueStyle }}">
                            <textarea rows="2"
                                      placeholder="Ex: Draft MoU envoyé, Rapport finalisé..."
                                      style="width:100%;font-size:13px;font-family:'DM Sans',sans-serif;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:8px 10px;resize:none;outline:none;color:var(--text-1);transition:border-color .15s;"
                                      onfocus="this.style.borderColor='var(--blue)'" onblur="this.style.borderColor='var(--border)'"
                                      wire:blur="saveField('deliverable', $event.target.value)">{{ $item->deliverable }}</textarea>
                        </div>
                    </div>

                    {{-- Journal des obstacles --}}
                    <div style="{{ $fieldStyle }} border-bottom:none;">
                        <span style="{{ $labelStyle }}">
                            Journal obstacles
                        </span>
                        <div style="{{ $valueStyle }}">
                            <textarea rows="3"
                                      placeholder="Décris les blocages rencontrés pour alerter les responsables..."
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
                                    <span style="font-size:11px;color:var(--text-3);font-family:'DM Mono',monospace;">
                                        {{ $comment->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                <div style="background:var(--bg);border-radius:12px;border-top-left-radius:4px;padding:10px 14px;font-size:13px;color:var(--text-1);line-height:1.5;">
                                    {!! nl2br(e(preg_replace('/@(\w+)/', '<span style="color:var(--blue);font-weight:500;">@$1</span>', $comment->body))) !!}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center;padding:32px;color:var(--text-3);">
                            <div style="font-size:24px;margin-bottom:8px;">💬</div>
                            <div style="font-size:13px;">Aucun commentaire — soyez le premier !</div>
                        </div>
                    @endforelse
                </div>
            @endif

            {{-- ─ TAB HISTORIQUE ─ --}}
            @if($activeTab === 'history')
                <div style="padding:16px 0;position:relative;">
                    <div style="position:absolute;left:15px;top:16px;bottom:16px;width:1px;background:var(--border);"></div>
                    @foreach($item->activityLog ?? [] as $log)
                        <div style="display:flex;gap:14px;align-items:flex-start;margin-bottom:16px;position:relative;">
                            <div style="width:8px;height:8px;border-radius:50%;background:var(--blue);flex-shrink:0;margin-top:4px;position:relative;z-index:1;"></div>
                            <div>
                                <div style="font-size:13px;color:var(--text-1);">{{ $log['description'] ?? '' }}</div>
                                <div style="font-size:11px;color:var(--text-3);font-family:'DM Mono',monospace;margin-top:2px;">{{ $log['time'] ?? '' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>

        {{-- ── ZONE COMMENTAIRE (sticky bottom) ── --}}
        @if($activeTab === 'comments')
            <div style="padding:14px 24px;border-top:1px solid var(--border);flex-shrink:0;">
                <div style="display:flex;gap:10px;align-items:flex-end;">
                    <div style="width:32px;height:32px;border-radius:50%;background:var(--text-1);color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 2)) }}
                    </div>
                    <textarea wire:model="newComment"
                              placeholder="Écrire un commentaire... (@mention)"
                              rows="2"
                              style="flex:1;font-size:13px;font-family:'DM Sans',sans-serif;border:1px solid var(--border);border-radius:10px;padding:8px 12px;resize:none;outline:none;color:var(--text-1);transition:border-color .15s;"
                              onfocus="this.style.borderColor='var(--blue)'" onblur="this.style.borderColor='var(--border)'"
                              wire:keydown.ctrl.enter="addComment()">
                    </textarea>
                    <button wire:click="addComment()"
                            style="width:36px;height:36px;background:var(--text-1);border:none;border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;transition:background .15s;"
                            onmouseover="this.style.background='#2a2a28'" onmouseout="this.style.background='var(--text-1)'">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M14 2L8 14l-2-5L1 5l13-3z" fill="white"/>
                        </svg>
                    </button>
                </div>
                <p style="font-size:11px;color:var(--text-3);margin-top:6px;margin-left:42px;">Ctrl+Entrée pour envoyer</p>
            </div>
        @endif

        @endif {{-- end if($item) --}}

    </div>
</div>