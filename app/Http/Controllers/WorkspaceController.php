<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\User;
use App\Models\Workspace;
use App\Http\Requests\WorkspaceMemberStoreRequest;
use App\Http\Requests\WorkspaceMemberUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        abort_unless($request->user()->can('view', $workspace), 403);

        $request->user()->forceFill([
            'current_workspace_id' => $workspace->id,
        ])->save();

        return redirect()->route('dashboard')->with('status', 'workspace-switched');
    }

    public function storeMember(WorkspaceMemberStoreRequest $request, Workspace $workspace)
    {
        $data = $request->validated();

        $member = User::where('email', $data['email'])->first();

        if (!$member) {
            return back()->withErrors([
                'email' => 'Aucun utilisateur ne correspond à cette adresse e-mail.',
            ], 'workspaceMembers');
        }

        $workspace->members()->syncWithoutDetaching([
            $member->id => ['role' => $data['role']],
        ]);

        if (!$member->current_workspace_id) {
            $member->forceFill([
                'current_workspace_id' => $workspace->id,
            ])->save();
        }

        return back()->with('status', 'workspace-member-added');
    }

    public function updateMember(WorkspaceMemberUpdateRequest $request, Workspace $workspace, User $user)
    {
        if ((int) $workspace->user_id === (int) $user->id) {
            abort(403);
        }

        abort_unless($workspace->members()->whereKey($user->id)->exists(), 404);

        $data = $request->validated();

        $workspace->members()->updateExistingPivot($user->id, [
            'role' => $data['role'],
        ]);

        return back()->with('status', 'workspace-member-updated');
    }

    public function destroyMember(Request $request, Workspace $workspace, User $user)
    {
        abort_unless($request->user()->can('manageMembers', $workspace), 403);

        if ((int) $workspace->user_id === (int) $user->id) {
            return back()->withErrors([
                'workspace' => 'Le propriétaire ne peut pas être retiré du workspace.',
            ], 'workspaceMembers');
        }

        abort_unless($workspace->members()->whereKey($user->id)->exists(), 404);

        $workspace->members()->detach($user->id);

        if ((int) $user->current_workspace_id === (int) $workspace->id) {
            $fallbackWorkspace = $user->workspaces()->whereKeyNot($workspace->id)->first();
            $user->forceFill([
                'current_workspace_id' => $fallbackWorkspace?->id,
            ])->save();
        }

        return back()->with('status', 'workspace-member-removed');
    }

    public function invite(Request $request, Workspace $workspace)
    {
        abort_unless($request->hasValidSignature(), 403);

        $role = $request->string('role')->toString() ?: 'member';
        abort_unless(in_array($role, ['admin', 'member', 'reader'], true), 403);

        if ($request->user()) {
            $workspace->members()->syncWithoutDetaching([
                $request->user()->id => ['role' => $role],
            ]);

            if (!$request->user()->current_workspace_id) {
                $request->user()->forceFill([
                    'current_workspace_id' => $workspace->id,
                ])->save();
            }

            return redirect()->route('members')->with('status', 'workspace-invite-accepted');
        }

        session([
            'pending_workspace_invite' => [
                'workspace_id' => $workspace->id,
                'role' => $role,
            ],
        ]);

        return redirect()->route('register')->with('status', 'workspace-invite-pending');
    }
}
