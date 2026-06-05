<?php

namespace App\Http\Controllers;

use App\Events\BoardUpdated;
use App\Http\Requests\BoardStoreRequest;
use App\Http\Requests\BoardUpdateRequest;
use App\Http\Requests\GroupStoreRequest;
use App\Http\Requests\ItemStoreRequest;
use App\Models\Board;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function store (BoardStoreRequest $request) {
        $data = $request->validated();

        $workspace = $request->user()?->activeWorkspace;

        abort_unless($workspace, 422, 'Aucun workspace actif pour créer un board.');

        $board = Board::create([
            'name' => $data['name'],
            'color' => $data['color'] ?? '#0091CD',
            'workspace_id' => $workspace->id,
        ]);

        return redirect()->route('boards.show', $board);
    }

    public function update (BoardUpdateRequest $request, Board $board) {
        $data = $request->validated();

        $board->update([
            'name' => trim($data['name']),
            'color' => $data['color'] ?? $board->color,
        ]);

        broadcast(new BoardUpdated($board->fresh(), 'board.updated', ['board_id' => $board->id]))->toOthers();

        return back()->with('status', 'board-updated');
    }

    public function destroy (Request $request, Board $board) {
        abort_unless($request->user()?->can('delete', $board), 403);

        $board->delete();

        return redirect()->route('boards.index')->with('status', 'board-deleted');
    }

    public function storeGroupe (GroupStoreRequest $request, Board $board)
    {
        $data = $request->validated();

        $group = $board->groups()->create([
            'name' => trim($data['name'] ?? '') ?: 'Nouveau groupe',
            'color' => $data['color'] ?? '#0091CD',
            'order' => ((int) $board->groups()->max('order')) + 1,
        ]);

        broadcast(new BoardUpdated($board->fresh(), 'group.created', ['group_id' => $group->id]))->toOthers();

        return redirect()->route('boards.show', ['board' => $board])->with('group_created_id', $group->id);
    }

    public function storeItem (ItemStoreRequest $request, Board $board)
    {

        $data = $request->validated();

        $redirectView = $data['redirect_view'] ?? 'table';
        $redirectParams = ['board' => $board];

        if ($redirectView === 'calendar') {
            if (isset($data['redirect_month'])) {
                $redirectParams['month'] = $data['redirect_month'];
            }
            if (isset($data['redirect_year'])) {
                $redirectParams['year'] = $data['redirect_year'];
            }
        }

        $redirectRoute = match ($redirectView) {
            'kanban' => 'boards.kanban',
            'calendar' => 'boards.calendar',
            default => 'boards.show',
        };

        $group = $board->groups()->find($data['group_id'] ?? null);

        if (!$group) {
            $group = $board->groups()->orderBy('order')->first();
        }

        if (!$group) {
            $group = $board->groups()->create([
                'name' => 'Général',
                'color' => '#0091CD',
                'order' => ((int) $board->groups()->max('order')) + 1,
            ]);
        }

        $item = $group->items()->create([
            'name' => trim($data['name']),
            'status' => $data['status'] ?? 'todo',
            'priority' => $data['priority'] ?? 'moyenne',
            'deadline' => $data['deadline'] ?? null,
            'description' => isset($data['description']) && filled($data['description'])
                ? strip_tags($data['description'], '<p><br><strong><em><a><ul><ol><li><blockquote><code>')
                : null,
            'deliverable' => $data['deliverable'] ?? null,
            'obstacles' => $data['obstacles'] ?? null,
            'order' => ((int) $group->items()->max('order')) + 1,
        ]);

        if (isset($data['assignees'])) {
            $item->assignees()->sync($data['assignees']);
        }

        broadcast(new BoardUpdated($board->fresh(), 'item.created', ['item_id' => $item->id]))->toOthers();

        return redirect()
            ->route($redirectRoute, $redirectParams)
            ->with('item_created_id', $item->id)
            ->with('item_created_name', $item->name);
    }
}
