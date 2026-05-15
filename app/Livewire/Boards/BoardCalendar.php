<?php
// app/Livewire/Boards/BoardCalendar.php

namespace App\Livewire\Boards;

use App\Models\Board;
use App\Models\Item;
use Carbon\Carbon;
use Livewire\Component;

class BoardCalendar extends Component
{
    public Board $board;
    public int   $year;
    public int   $month;

    public function mount(Board $board): void
    {
        $this->board = $board;
        $this->year  = (int) request()->integer('year', now()->year);
        $this->month = (int) request()->integer('month', now()->month);
    }

    public function prevMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year  = $date->year;
        $this->month = $date->month;
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year  = $date->year;
        $this->month = $date->month;
    }

    public function goToday(): void
    {
        $this->year  = now()->year;
        $this->month = now()->month;
    }

    public function openItemPanel(int $itemId): void
    {
        $this->dispatch('open-item-panel', itemId: $itemId);
    }

    public function render()
    {
        $startOfMonth = Carbon::create($this->year, $this->month, 1);
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        $items = $this->board
            ->items()
            ->with('assignees')
            ->whereNotNull('deadline')
            ->whereBetween('deadline', [$startOfMonth, $endOfMonth])
            ->get()
            ->groupBy(fn($item) => Carbon::parse($item->deadline)->format('Y-m-d'));

        // Grille lundi → dimanche
        $startOfGrid = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $endOfGrid   = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        $weeks = [];
        $current = $startOfGrid->copy();
        while ($current <= $endOfGrid) {
            $week = [];
            for ($d = 0; $d < 7; $d++) {
                $week[] = $current->copy();
                $current->addDay();
            }
            $weeks[] = $week;
        }

        $monthLabel = $startOfMonth->isoFormat('MMMM YYYY');
        $groups = $this->board
            ->groups()
            ->orderBy('order')
            ->get();

        return view('livewire.boards.board-calendar', [
            'weeks'      => $weeks,
            'items'      => $items,
            'monthLabel' => $monthLabel,
            'groups'     => $groups,
        ]);
    }
}
