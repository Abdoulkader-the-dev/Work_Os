<?php

namespace App\Http\Controllers;

use App\Http\Requests\MeetingStoreRequest;
use App\Http\Requests\MeetingUpdateRequest;
use App\Models\Board;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Http\Request;
use App\Events\MeetingUpdated;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Add this

class MeetingController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        return view('pages.meetings');
    }

    public function create()
    {
        return view('pages.meeting-create');
    }

    public function edit(Meeting $meeting)
    {
        $this->authorize('update', $meeting);
        return view('pages.meeting-edit', compact('meeting'));
    }

    public function store(MeetingStoreRequest $request)
    {
        $data = $request->validated();
        $workspace = $request->user()->activeWorkspace;

        abort_unless($workspace, 422, 'Aucun workspace actif.');

        $meeting = Meeting::create([
            'title' => $data['title'],
            'date' => $data['date'],
            'workspace_id' => $workspace->id,
            'attendees' => $data['attendees'] ?? [],
            'bilan' => $data['bilan'] ?? [],
            'recommendations' => $data['recommendations'] ?? [],
            'actions' => $data['actions'] ?? [],
            'user_id' => $request->user()->id,
        ]);

        broadcast(new MeetingUpdated($meeting, 'created'))->toOthers();

        return response()->json(['meeting' => $meeting], 201);
    }

    public function show(Meeting $meeting)
    {
        $this->authorize('view', $meeting);

        return view('pages.meeting-show', compact('meeting'));
    }

    public function update(MeetingUpdateRequest $request, Meeting $meeting)
    {
        $data = $request->validated();

        $meeting->update($data);

        broadcast(new MeetingUpdated($meeting, 'updated'))->toOthers();

        return response()->json(['meeting' => $meeting], 200);
    }

    public function destroy(Meeting $meeting)
{
    $this->authorize('delete', $meeting);

    $meeting->delete();

    // broadcast(new MeetingUpdated($meeting, 'deleted'))->toOthers();

    return response()->json(['message' => 'Meeting deleted'], 200);
}

    public function convertActionToTask(Request $request, Meeting $meeting, $actionIndex)
    {
        $this->authorize('update', $meeting);

        $actions = $meeting->actions;

        if (!isset($actions[$actionIndex])) {
            return response()->json(['message' => 'Action not found'], 404);
        }

        $action = $actions[$actionIndex];

        if ($action['converted'] ?? false) {
            return response()->json(['message' => 'Action already converted'], 400);
        }

        $workspace = $meeting->workspace ?? $request->user()->activeWorkspace;
        abort_unless($workspace, 422, 'Aucun workspace actif.');

        $board = Board::where('workspace_id', $workspace->id)->first();

        if (!$board) {
            return response()->json(['message' => 'No board found'], 404);
        }

        $group = $board->groups()->firstOrCreate(
            ['name' => 'Réunions'],
            ['color' => '#0091CD', 'order' => 99]
        );

        $item = $group->items()->create([
            'name' => $action['text'],
            'status' => 'todo',
            'priority' => 'moyenne',
            'deadline' => $action['deadline'] ?? null,
        ]);

        if (!empty($action['assignee_id'])) {
            $assignee = User::find($action['assignee_id']);
            abort_unless(
                $assignee && $assignee->belongsToWorkspace($board->workspace),
                422,
                'L\'assigné doit appartenir au workspace.'
            );
            $item->assignees()->attach($action['assignee_id']);
        }

        $actions[$actionIndex]['converted'] = true;
        $actions[$actionIndex]['item_id'] = $item->id;

        $meeting->update(['actions' => $actions]);

        return response()->json([
            'message' => 'Action converted to task',
            'item' => $item
        ], 200);
    }

    public function addAttendee(Request $request, Meeting $meeting)
    {
        $this->authorize('update', $meeting);

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $attendees = $meeting->attendees ?? [];
        $attendees[] = $request->name;
        $meeting->update(['attendees' => $attendees]);

        return response()->json(['message' => 'Attendee added', 'attendees' => $attendees], 200);
    }

    public function removeAttendee(Meeting $meeting, $index)
    {
        $this->authorize('update', $meeting);

        $attendees = $meeting->attendees ?? [];

        if (!isset($attendees[$index])) {
            return response()->json(['message' => 'Attendee not found'], 404);
        }

        array_splice($attendees, $index, 1);
        $meeting->update(['attendees' => $attendees]);

        return response()->json(['message' => 'Attendee removed', 'attendees' => $attendees], 200);
    }

    public function addBilanPoint(Request $request, Meeting $meeting)
    {
        $this->authorize('update', $meeting);

        $request->validate([
            'point' => 'required|string|max:5000'
        ]);

        $bilan = $meeting->bilan ?? [];
        $bilan[] = $request->point;
        $meeting->update(['bilan' => $bilan]);

        return response()->json(['message' => 'Bilan point added', 'bilan' => $bilan], 200);
    }

    public function removeBilanPoint(Meeting $meeting, $index)
    {
        $this->authorize('update', $meeting);

        $bilan = $meeting->bilan ?? [];

        if (!isset($bilan[$index])) {
            return response()->json(['message' => 'Bilan point not found'], 404);
        }

        array_splice($bilan, $index, 1);
        $meeting->update(['bilan' => $bilan]);

        return response()->json(['message' => 'Bilan point removed', 'bilan' => $bilan], 200);
    }

    public function addRecommendation(Request $request, Meeting $meeting)
    {
        $this->authorize('update', $meeting);

        $request->validate([
            'recommendation' => 'required|string|max:5000'
        ]);

        $recommendations = $meeting->recommendations ?? [];
        $recommendations[] = $request->recommendation;
        $meeting->update(['recommendations' => $recommendations]);

        return response()->json(['message' => 'Recommendation added', 'recommendations' => $recommendations], 200);
    }

    public function removeRecommendation(Meeting $meeting, $index)
    {
        $this->authorize('update', $meeting);

        $recommendations = $meeting->recommendations ?? [];

        if (!isset($recommendations[$index])) {
            return response()->json(['message' => 'Recommendation not found'], 404);
        }

        array_splice($recommendations, $index, 1);
        $meeting->update(['recommendations' => $recommendations]);

        return response()->json(['message' => 'Recommendation removed', 'recommendations' => $recommendations], 200);
    }

    public function addAction(Request $request, Meeting $meeting)
    {
        $this->authorize('update', $meeting);

        $workspace = $meeting->workspace ?? $request->user()->activeWorkspace;
        abort_unless($workspace, 422, 'Aucun workspace actif.');

        $request->validate([
            'text' => 'required|string|max:255',
            'assignee_id' => ['nullable', 'integer'],
            'deadline' => 'nullable|date',
        ]);

        if ($request->filled('assignee_id')) {
            $assignee = User::find($request->assignee_id);
            abort_unless(
                $assignee && $assignee->belongsToWorkspace($workspace),
                422,
                'L\'assigné doit appartenir au workspace.'
            );
        }

        $actions = $meeting->actions ?? [];
        $actions[] = [
            'text' => $request->text,
            'assignee_id' => $request->assignee_id,
            'deadline' => $request->deadline,
            'converted' => false,
            'item_id' => null,
        ];
        $meeting->update(['actions' => $actions]);

        return response()->json(['message' => 'Action added', 'actions' => $actions], 200);
    }

    public function removeAction(Meeting $meeting, $index)
    {
        $this->authorize('update', $meeting);

        $actions = $meeting->actions ?? [];

        if (!isset($actions[$index])) {
            return response()->json(['message' => 'Action not found'], 404);
        }

        array_splice($actions, $index, 1);
        $meeting->update(['actions' => $actions]);

        return response()->json(['message' => 'Action removed', 'actions' => $actions], 200);
    }

    public function exportPdf(Meeting $meeting)
    {
        $this->authorize('view', $meeting);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.meeting', compact('meeting'));

        return $pdf->stream('compte-rendu-' . $meeting->id . '.pdf');
    }
}
