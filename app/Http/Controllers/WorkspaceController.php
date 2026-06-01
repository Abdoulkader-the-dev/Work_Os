<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Workspace;
use Illuminate\Support\Facades\Log;

class WorkspaceController extends Controller
{
    public function addMember(Request $request, int $workspace)
    {
        Log::info('Controller reached!', ['workspace' => $workspace]);

        // Don't use $request->user()->workspaces() - just find directly
        $workspace = Workspace::findOrFail($workspace);

        // Optional: Check if user owns the workspace
        if ($workspace->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'id' => 'required|exists:users,id',
            'role' => 'required|in:member,admin',
        ]);

        // Use members() relationship, not users()
        $workspace->members()->attach($request->id, ['role' => $request->role]);

        return response()->json(['message' => 'Member added successfully'], 200);
    }
}
