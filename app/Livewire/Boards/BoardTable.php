<?php

namespace App\Livewire\Boards;

use App\Models\Board;
use Livewire\Component;

class BoardTable extends Component
{
    public Board $board;

    public function mount(Board $board): void
    {
        $this->board = $board;
    }

    public function render()
{
    $groups = $this->board
        ->groups()
        ->with(['items' => fn($q) => $q->with('assignees')->orderBy('order')])
        ->orderBy('order')
        ->get();

    return view('livewire.boards.board-table', compact('groups'));
}
}
