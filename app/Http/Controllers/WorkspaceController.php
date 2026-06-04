<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkspaceController extends Controller
{

    public  function store (Request $request)
    {

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => 'Le nom est requis.',
        ]);

        $workspace = DB::transaction(function () use ($request, $data) {
            $workspace = Workspace::create([
                'name' => trim($data['name']),
                'user_id' => $request->user()->id,
                'color' => '#0091CD',
            ]);

            $request->user()->workspaces()->syncWithoutDetaching([
                $workspace->id => ['role' => 'admin'],
            ]);

            $request->user()->forceFill([
                'current_workspace_id' => $workspace->id,
            ])->save();

            $request->user()->markOnboardingStarted('dashboard');

            return $workspace;
        });

        return redirect()->route('dashboard')->with('status', 'workspace-created')->with('onboarding', 'start');
    }

    public function addMember(Request $request, int $workspace)
    {
        Log::info('Controller reached!', ['workspace' => $workspace]);

        // Don't use $request->user()->workspaces() - just find directly
        $workspace = Workspace::findOrFail($workspace);

        $isAdmin = $workspace->members()
            ->where('user_id', $request->user()->id)
            ->where('role', 'admin')
            ->exists();

        if (!$isAdmin) {
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

    public function update (Request $request, Workspace $workspace) {
        abort_unless($request->user()->can('update', $workspace), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => 'Le nom est requis.',
        ]);

        $workspace->update([
            'name' => trim($data['name']),
        ]);

        return redirect()->route('dashboard')->with('status', 'workspace-updated');
    }

    public function destroy (Request $request, Workspace $workspace) {
        abort_unless($request->user()->can('delete', $workspace), 403);

        $user = $request->user();
        $workspaceId = $workspace->id;

        if ((int) $user->current_workspace_id === (int) $workspaceId) {
            $fallbackWorkspace = $user->workspaces()->whereKeyNot($workspaceId)->first();
            $user->forceFill([
                'current_workspace_id' => $fallbackWorkspace?->id,
            ])->save();
        }

        $user->workspaces()->detach($workspaceId);
        $workspace->delete();

        return redirect()->route('dashboard')->with('status', 'workspace-deleted');
    }

    public function switch (Request $request, Workspace $workspace) {
        abort_unless($request->user()->workspaces()->whereKey($workspace->id)->exists(), 403);

        $request->user()->forceFill([
            'current_workspace_id' => $workspace->id,
        ])->save();

        return redirect()->route('dashboard')->with('status', 'workspace-switched');
    }

    public function removeMember(Request $request, int $workspace, int $user)
    {
        $workspace = Workspace::findOrFail($workspace);
        $memberToRemove = User::findOrFail($user);

        // Check if current user is an ADMIN of the workspace
        $isAdmin = $workspace->members()
            ->where('user_id', $request->user()->id)
            ->where('role', 'admin')
            ->exists();

        if (!$isAdmin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if member exists in workspace
        if (!$workspace->members()->where('user_id', $memberToRemove->id)->exists()) {
            return response()->json(['error' => 'Member not found in workspace'], 404);
        }

        // Prevent removing the workspace owner (if you want to keep this rule)
        if ((int) $workspace->user_id === (int) $memberToRemove->id) {
            return response()->json(['error' => 'Cannot remove the workspace owner'], 403);
        }

        // Remove the member
        $workspace->members()->detach($memberToRemove->id);

        // If the removed member's current workspace was this one, switch them to another workspace
        if ((int) $memberToRemove->current_workspace_id === (int) $workspace->id) {
            $fallbackWorkspace = $memberToRemove->workspaces()->first();
            $memberToRemove->forceFill([
                'current_workspace_id' => $fallbackWorkspace?->id,
            ])->save();
        }

        return response()->json(['message' => 'Member removed successfully'], 200);
    }

    public function changeMemberRole(Request $request, int $workspace, int $user)
    {
        $workspace = Workspace::findOrFail($workspace);
        $member = User::findOrFail($user);

        // Check if current user is an ADMIN of the workspace
        $isAdmin = $workspace->members()
            ->where('user_id', $request->user()->id)
            ->where('role', 'admin')
            ->exists();

        if (!$isAdmin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Prevent changing the role of the workspace owner
        if ((int) $workspace->user_id === (int) $member->id) {
            return response()->json(['error' => 'Cannot change the role of the workspace owner'], 403);
        }

        // Validate the new role
        $request->validate([
            'role' => 'required|in:member,admin'
        ]);

        // Check if member exists in workspace
        if (!$workspace->members()->where('user_id', $member->id)->exists()) {
            return response()->json(['error' => 'Member not found in workspace'], 404);
        }

        // Update the role
        $workspace->members()->updateExistingPivot($member->id, [
            'role' => $request->role
        ]);

        return response()->json(['message' => 'Member role changed successfully'], 200);
    } 
}
