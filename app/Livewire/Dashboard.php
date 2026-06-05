<?php

namespace App\Livewire;

use App\Models\Board;
use App\Models\Item;
use App\Models\Meeting;
use Livewire\Component;

class Dashboard extends Component
{
    public $workspace;
    
    public function refresh()
    {
        // Re-render
    }

    protected function getListeners(): array
    {
        $workspaceId = auth()->user()?->activeWorkspace?->id;

        $listeners = [
            'meeting-saved' => 'refresh',
            'action-converted' => 'refresh',
            'workspace-changed' => 'refresh',
        ];

        if ($workspaceId) {
            $listeners["echo-private:workspaces.{$workspaceId},BoardUpdated"] = 'refresh';
            $listeners["echo-private:workspaces.{$workspaceId},MeetingUpdated"] = 'refresh';
        }

        return $listeners;
    }

    public function render()
    {
        $user = auth()->user();
        $this->workspace = $user?->activeWorkspace;

        if ($user && $user->shouldShowOnboarding()) {
            $user->markOnboardingStarted('dashboard');
        }

        if (!$this->workspace) {
            return view('livewire.dashboard', [
                'workspace' => null,
                'completionRate' => 0,
                'doneTasks' => 0,
                'totalTasks' => 0,
                'tasksThisWeek' => 0,
                'weeklyDelta' => 0,
                'activeBoards' => 0,
                'tasksDueThisWeek' => 0,
                'recentActivity' => collect(),
                'boardsPreview' => collect(),
                'urgentTasks' => collect(),
                'blockedTasks' => 0,
                'members' => collect(),
            ]);
        }

        $boardQuery = Board::query()
            ->where('workspace_id', $this->workspace?->id);

        $itemQuery = Item::query()
            ->whereHas('group.board', fn ($query) => $query->where('workspace_id', $this->workspace?->id));

        $boards = (clone $boardQuery)->get();
        $activeBoards = $boards->count();
        $totalTasks = (clone $itemQuery)->count();
        $doneTasks = (clone $itemQuery)->where('status', 'done')->count();
        $blockedTasks = (clone $itemQuery)->where('status', 'blocked')->count();
        $completionRate = $totalTasks > 0 ? (int) round(($doneTasks / $totalTasks) * 100) : 0;

        $weekStart = now()->copy()->startOfWeek();
        $weekEnd = now()->copy()->endOfWeek();
        $lastWeekStart = $weekStart->copy()->subWeek();
        $lastWeekEnd = $weekEnd->copy()->subWeek();

        $tasksThisWeek = (clone $itemQuery)->whereBetween('created_at', [$weekStart, $weekEnd])->count();
        $tasksLastWeek = (clone $itemQuery)->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->count();
        $weeklyDelta = $tasksLastWeek > 0
            ? (int) round((($tasksThisWeek - $tasksLastWeek) / $tasksLastWeek) * 100)
            : ($tasksThisWeek > 0 ? 100 : 0);

        $tasksDueThisWeek = (clone $itemQuery)
            ->whereNotNull('deadline')
            ->whereBetween('deadline', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->count();

        $boardsPreview = (clone $boardQuery)->withCount('items')->take(5)->get();
        
        $urgentTasks = (clone $itemQuery)
            ->where(function ($query) {
                $query->where('status', 'blocked')
                    ->orWhere('priority', 'critique')
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('deadline')
                            ->whereDate('deadline', '<=', now()->addDays(3)->toDateString())
                            ->where('status', '!=', 'done');
                    });
            })
            ->with('group.board')
            ->orderByRaw("case when status = 'blocked' then 0 when priority = 'critique' then 1 else 2 end")
            ->orderBy('deadline')
            ->take(6)
            ->get();

        $recentItems = (clone $itemQuery)
            ->with('group.board')
            ->latest('updated_at')
            ->take(4)
            ->get()
            ->map(fn ($item) => [
                'title' => $item->name,
                'subtitle' => ($item->group->board->name ?? 'Board') . ' / ' . ($item->group->name ?? 'Groupe'),
                'time' => $item->updated_at,
                'badge' => match ($item->status) {
                    'done' => 'Achevé',
                    'progress' => 'En cours',
                    'blocked' => 'Bloqué',
                    'ongoing' => 'Continu',
                    default => 'Non commencé',
                },
            ]);

        $recentMeetings = Meeting::query()
            ->where(function ($query) use ($user) {
                $query->where('workspace_id', $this->workspace?->id)
                    ->orWhere(function ($legacy) use ($user) {
                        $legacy->whereNull('workspace_id')
                            ->where('user_id', $user?->id);
                    });
            })
            ->latest('date')
            ->take(3)
            ->get()
            ->map(fn ($meeting) => [
                'title' => $meeting->title,
                'subtitle' => 'Réunion',
                'time' => $meeting->updated_at ?? $meeting->created_at,
                'badge' => 'Meeting',
            ]);

        $recentActivity = $recentItems
            ->concat($recentMeetings)
            ->sortByDesc('time')
            ->take(6)
            ->values();

        $members = collect([$this->workspace?->owner])
            ->merge($this->workspace?->members ?? collect())
            ->filter()
            ->unique('id')
            ->take(5)
            ->values();

        return view('livewire.dashboard', [
            'completionRate' => $completionRate,
            'doneTasks' => $doneTasks,
            'totalTasks' => $totalTasks,
            'tasksThisWeek' => $tasksThisWeek,
            'weeklyDelta' => $weeklyDelta,
            'activeBoards' => $activeBoards,
            'tasksDueThisWeek' => $tasksDueThisWeek,
            'recentActivity' => $recentActivity,
            'boardsPreview' => $boardsPreview,
            'urgentTasks' => $urgentTasks,
            'blockedTasks' => $blockedTasks,
            'members' => $members,
            'workspace' => $this->workspace,
        ]);
    }
}
