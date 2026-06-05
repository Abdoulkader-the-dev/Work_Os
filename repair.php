<?php

$file = __DIR__ . '/resources/views/livewire/items/item-panel.blade.php';
$content = file_get_contents($file);

$search = <<<EOT
                       wire:blur="saveField('name', \$event.target.value)">
                    @endif
                </button>
            @endforeach
        </div>

        {{-- ── CONTENT ── --}}
EOT;

$replace = <<<EOT
                       wire:blur="saveField('name', \$event.target.value)">

                {{-- Badge obstacles --}}
                @if(\$item->obstacles)
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
                <a href="{{ route('boards.show', \$item->group->board) }}"
                   style="font-size:11px;color:var(--blue);text-decoration:none;font-family:'DM Mono',monospace;">
                    {{ \$item->group->board->name ?? 'Sans tableau' }}
                </a>
                <span style="color:var(--text-3);font-size:11px;">/</span>
                <span style="font-size:11px;color:var(--text-3);font-family:'DM Mono',monospace;">
                    {{ \$item->group->name ?? 'Sans groupe' }}
                </span>
            </div>
        </div>

        {{-- ── TABS ── --}}
        <div style="display:flex;padding:0 24px;border-bottom:1px solid var(--border);flex-shrink:0;">
            @foreach(['details' => 'Détails', 'comments' => 'Commentaires', 'history' => 'Historique'] as \$tab => \$label)
                <button wire:click="\$set('activeTab', '{{ \$tab }}')"
                        style="padding:12px 0;margin-right:24px;font-size:13px;font-weight:500;background:none;border:none;border-bottom:2px solid {{ \$activeTab === \$tab ? 'var(--text-1)' : 'transparent' }};color:{{ \$activeTab === \$tab ? 'var(--text-1)' : 'var(--text-2)' }};cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .15s;white-space:nowrap;">
                    {{ \$label }}
                    @if(\$tab === 'comments' && \$item->comments->count() > 0)
                        <span style="font-size:10px;font-family:'DM Mono',monospace;background:var(--bg);padding:1px 5px;border-radius:10px;margin-left:4px;">{{ \$item->comments->count() }}</span>
                    @endif
                </button>
            @endforeach
        </div>

        {{-- ── CONTENT ── --}}
EOT;

$newContent = str_replace($search, $replace, $content);
file_put_contents($file, $newContent);
echo "File repaired.\n";
