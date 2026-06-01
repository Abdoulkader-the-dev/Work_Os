<div style="display:flex;flex-direction:column;gap:24px;">
    <div wire:loading.flex style="align-items:center;gap:8px;color:var(--text-3);font-size:13px;">
        <svg class="animate-spin" width="14" height="14" viewBox="0 0 14 14" fill="none">
            <circle cx="7" cy="7" r="5" stroke="currentColor" stroke-width="1.5" stroke-dasharray="16" stroke-linecap="round" opacity=".35"/>
            <path d="M7 2a5 5 0 015 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        Chargement du workspace...
    </div>

    @if(session('status') === 'workspace-created')
        <div class="badge s-done" style="padding:12px 14px;width:100%;justify-content:flex-start;border-radius:10px;">
            Workspace créé et activé.
        </div>
    @endif

    @if(!$workspace)
        {{-- Empty State --}}
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:80px 20px;text-align:center;">
            <div style="width:80px;height:80px;background:var(--blue-light);border-radius:24px;display:flex;align-items:center;justify-content:center;margin-bottom:24px;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" style="color:var(--blue);">
                    <path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M12 11v6M9 14h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </div>
            <h2 class="text-2xl font-semibold text-1" style="margin-bottom:12px;">Bienvenue sur UniPod</h2>
            <p class="text-md text-3" style="max-width:400px;margin-bottom:32px;line-height:1.6;">
                Commencez par créer votre premier workspace pour organiser vos projets, vos tâches et vos réunions.
            </p>
            <button type="button" 
                    onclick="wsShowCreating(event); document.querySelector('[data-dropdown-trigger=workspace-menu]').click()"
                    class="btn-primary" style="padding:12px 24px;font-size:14px;">
                Créer mon premier workspace
            </button>
        </div>
    @else
        <div class="flex-between flex-wrap" style="gap:14px;">
            <div>
                <h2 class="text-2xl font-semibold text-1" style="letter-spacing:-0.03em;line-height:1;">Aujourd'hui</h2>
                <p class="text-xs text-3 f-mono" style="margin-top:6px;">
                    {{ now()->isoFormat('ddd D MMM · HH:mm') }}
                </p>
            </div>

            <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
                <div class="bento-card flex-center" style="gap:16px;padding:14px 20px;">
                    <div>
                        <div class="text-sm font-medium">Workspace actif</div>
                        <div class="text-xl font-semibold text-1" style="letter-spacing:-0.02em;margin-top:2px;">
                            {{ $workspace?->name ?? 'Aucun workspace' }}
                        </div>
                    </div>
                    <div style="width:42px;height:42px;background:var(--yellow-light);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" style="color:var(--yellow);">
                            <path d="M3 5.5a2 2 0 012-2h3.25a1 1 0 01.71.29L10.17 5H13a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-7.5z" stroke="currentColor" stroke-width="1.6"/>
                        </svg>
                    </div>
                </div>

                <div class="bento-card"
                     style="display:flex;align-items:center;gap:16px;padding:14px 20px;cursor:pointer;"
                     x-data="{
                        running: false,
                        seconds: 0,
                        timer: null,
                        get display() {
                            const h = String(Math.floor(this.seconds / 3600)).padStart(2, '0');
                            const m = String(Math.floor((this.seconds % 3600) / 60)).padStart(2, '0');
                            const s = String(this.seconds % 60).padStart(2, '0');
                            return `${h}:${m}:${s}`;
                        },
                        toggle() {
                            this.running = !this.running;
                            if (this.running) {
                                this.timer = setInterval(() => this.seconds++, 1000);
                            } else {
                                clearInterval(this.timer);
                            }
                        }
                     }"
                     @click="toggle()">
                    <div>
                        <div class="text-sm font-medium">Démarrer le chrono</div>
                        <div class="text-xl font-semibold text-1 f-mono" style="letter-spacing:-0.02em;margin-top:2px;"
                             x-text="display">00:00:00</div>
                    </div>
                    <div style="width:42px;height:42px;background:var(--yellow);border-radius:50%;display:flex;align-items:center;justify-content:center;transition:transform .2s;flex-shrink:0;"
                         :style="running ? 'transform:scale(1.08)' : ''">
                        <svg x-cloak x-show="!running" width="16" height="16" viewBox="0 0 16 16" fill="none" style="margin-left:2px;">
                            <path d="M5 3l9 5-9 5V3z" fill="#111110"/>
                        </svg>
                        <svg x-cloak x-show="running" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <rect x="3" y="3" width="4" height="10" rx="1" fill="#111110"/>
                            <rect x="9" y="3" width="4" height="10" rx="1" fill="#111110"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="kpi-grid">
            <x-kpi-card
                label="Progression globale"
                value="{{ $completionRate }}%"
                subvalue="{{ $doneTasks }} / {{ $totalTasks }} tâches achevées"
            />

            <x-kpi-card
                label="Créées cette semaine"
                value="{{ str_pad((string) $tasksThisWeek, 2, '0', STR_PAD_LEFT) }}"
                delta="{{ $weeklyDelta >= 0 ? '+' : '' }}{{ $weeklyDelta }}% vs semaine passée"
                deltaColor="{{ $weeklyDelta >= 0 ? '#16a34a' : '#dc2626' }}"
            />

            <x-kpi-card
                label="Boards actifs"
                value="{{ str_pad((string) $activeBoards, 2, '0', STR_PAD_LEFT) }}"
                subvalue="Workspace courant"
            />

            <x-kpi-card
                label="À traiter cette semaine"
                value="{{ str_pad((string) $tasksDueThisWeek, 2, '0', STR_PAD_LEFT) }}"
                subvalue="Tâches avec deadline"
            />
        </div>

        <div style="display:grid;grid-template-columns:1.15fr .85fr;gap:14px;">
            <div class="bento-card" style="padding:20px;">
                <x-section-header title="Activité récente" link="{{ route('notifications.index') }}" linkText="Notifications" wire:navigate />
                
                <div style="display:flex;flex-direction:column;">
                    @forelse($recentActivity as $event)
                        <div class="flex-between" style="align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);">
                            <div class="user-avatar text-xs font-semibold" style="width:32px;height:32px;">
                                {{ strtoupper(substr($event['title'], 0, 2)) }}
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div class="text-sm font-medium text-1">{{ $event['title'] }}</div>
                                <div class="text-xs text-2" style="margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $event['subtitle'] }}
                                </div>
                                <div class="text-xs text-3 f-mono" style="margin-top:2px;">
                                    {{ $event['time']->diffForHumans() }}
                                </div>
                            </div>
                            <span class="badge" style="background:var(--bg);color:var(--text-2);">
                                {{ $event['badge'] }}
                            </span>
                        </div>
                    @empty
                        <div style="text-align:center;padding:32px 0;color:var(--text-3);font-size:13px;">
                            Aucune activité récente
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="bento-card" style="padding:20px;display:flex;flex-direction:column;">
                <x-section-header title="Boards" link="{{ route('boards.index') }}" linkText="Voir tout" wire:navigate />

                <div style="flex:1;display:flex;flex-direction:column;gap:2px;">
                    @forelse($boardsPreview as $board)
                        <a href="{{ route('boards.show', $board) }}" wire:navigate
                           class="nav-item" style="color:inherit;padding:8px 10px;">
                            <div style="width:8px;height:8px;border-radius:50%;background:{{ $board->color ?? '#0091CD' }};flex-shrink:0;"></div>
                            <span class="text-sm font-medium" style="flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $board->name }}</span>
                            <div style="width:80px;height:3px;background:var(--bg);border-radius:10px;flex-shrink:0;overflow:hidden;">
                                <div style="height:100%;background:var(--blue);border-radius:10px;width:{{ $board->progress }}%;transition:width .4s;"></div>
                            </div>
                            <span class="text-xs text-3 f-mono" style="flex-shrink:0;">{{ $board->items_count }}</span>
                        </a>
                    @empty
                        <div style="text-align:center;padding:24px 0;color:var(--text-3);font-size:13px;">Aucun board actif</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="bento-card" style="padding:20px;">
            <x-section-header title="Tâches urgentes" link="{{ route('my-tasks') }}" wire:navigate>
                @if($blockedTasks > 0)
                    <span class="badge s-blocked f-mono" style="font-size:10px;">
                        {{ $blockedTasks }} bloquées
                    </span>
                @endif
            </x-section-header>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px;">
                @foreach($urgentTasks as $task)
                    <a href="{{ route('boards.show', $task->group->board) }}" wire:navigate
                       style="display:flex;align-items:flex-start;gap:10px;padding:12px;background:var(--bg);border-radius:10px;cursor:pointer;transition:background .15s;text-decoration:none;color:inherit;"
                       onmouseover="this.style.background='#eaeae8'" onmouseout="this.style.background='var(--bg)'">
                        <div style="width:6px;height:6px;border-radius:50%;margin-top:5px;flex-shrink:0;background:{{ $task->status === 'blocked' ? '#dc2626' : ($task->priority === 'critique' ? '#8b5cf6' : '#f97316') }};"></div>
                        <div style="flex:1;min-width:0;">
                            <div class="text-sm font-medium" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $task->name }}</div>
                            <div class="text-xs text-3 f-mono" style="margin-top:3px;">
                                {{ $task->group->board->name ?? '—' }} · {{ $task->deadline?->format('d M') ?? 'Sans deadline' }}
                            </div>
                        </div>
                        <x-status-badge status="{{ $task->status }}" style="font-size:10px;flex-shrink:0;" />
                    </a>
                @endforeach
                @if($urgentTasks->isEmpty())
                    <div style="grid-column:1/-1;text-align:center;padding:16px;color:var(--text-3);font-size:13px;">
                        Aucune tâche urgente dans ce workspace.
                    </div>
                @endif
            </div>
        </div>

        <div class="bento-card" style="padding:20px;margin-bottom:24px;">
            <x-section-header title="Membres" link="{{ route('members') }}" linkText="Gérer →" wire:navigate>
                <span class="text-xs text-3 f-mono" style="background:var(--bg);padding:1px 7px;border-radius:20px;">
                    {{ $members->count() }}
                </span>
            </x-section-header>

            <div style="overflow-x:auto;">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Membre</th>
                            <th>Tâches ouvertes</th>
                            <th>En cours</th>
                            <th>Rôle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $member)
                            @php
                                $memberTaskQuery = $member->items()->whereHas('group.board', fn ($query) => $query->where('workspace_id', $workspace?->id));
                                $memberOpenTasks = (clone $memberTaskQuery)->where('status', '!=', 'done')->count();
                                $memberInProgress = (clone $memberTaskQuery)->where('status', 'progress')->count();
                                $memberRole = $workspace && $workspace->user_id === $member->id ? 'Owner' : ($member->pivot->role ?? 'Membre');
                            @endphp
                            <tr style="transition:background .15s;" onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''">
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <div class="user-avatar text-xs font-semibold" style="width:32px;height:32px;">
                                            {{ strtoupper(substr($member->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-1">{{ $member->name }}</div>
                                            <div class="text-xs text-3">{{ $member->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-sm text-2">{{ $memberOpenTasks }}</td>
                                <td class="text-sm text-2">{{ $memberInProgress }}</td>
                                <td class="text-xs text-2" style="padding-left:10px;">{{ $memberRole }}</td>
                            </tr>
                        @endforeach
                        @if($members->isEmpty())
                            <tr>
                                <td colspan="4" style="padding:24px;text-align:center;color:var(--text-3);font-size:13px;border-top:1px solid var(--border);">
                                    Aucun membre dans ce workspace.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
