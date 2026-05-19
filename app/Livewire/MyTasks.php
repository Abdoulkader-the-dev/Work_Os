<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class MyTasks extends Component
{
    #[On('echo:board-updated,BoardUpdated')]
    public function refresh() {}

    public function render()
    {
        $items = auth()->user()?->items()
            ->with('group.board')
            ->orderByRaw("case when status = 'blocked' then 0 when status = 'progress' then 1 when status = 'todo' then 2 when status = 'ongoing' then 3 when status = 'done' then 4 else 5 end")
            ->orderBy('deadline')
            ->get() ?? collect();

        return view('livewire.my-tasks', [
            'items' => $items,
        ]);
    }
}
