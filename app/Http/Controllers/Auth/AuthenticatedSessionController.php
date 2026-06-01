<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Workspace;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $pendingInvite = $request->session()->pull('pending_workspace_invite');
        if (is_array($pendingInvite) && !empty($pendingInvite['workspace_id'])) {
            $workspace = Workspace::find($pendingInvite['workspace_id']);
            if ($workspace) {
                $role = in_array(($pendingInvite['role'] ?? 'member'), ['admin', 'member', 'reader'], true)
                    ? $pendingInvite['role']
                    : 'member';

                $request->user()->workspaces()->syncWithoutDetaching([
                    $workspace->id => ['role' => $role],
                ]);

                $request->user()->forceFill([
                    'current_workspace_id' => $workspace->id,
                ])->save();

                return redirect()->route('members')->with('status', 'workspace-invite-accepted');
            }
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
