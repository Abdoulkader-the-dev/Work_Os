<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Créer un workspace par défaut pour le nouvel utilisateur
        $workspace = Workspace::create([
            'name' => $request->name . "'s Workspace",
            'user_id' => $user->id,
            'color' => '#0091CD',
        ]);
        $user->workspaces()->attach($workspace->id, ['role' => 'admin']);
        $user->update(['current_workspace_id' => $workspace->id]);

        $pendingInvite = $request->session()->pull('pending_workspace_invite');
        if (is_array($pendingInvite) && !empty($pendingInvite['workspace_id'])) {
            $inviteWorkspace = Workspace::find($pendingInvite['workspace_id']);
            if ($inviteWorkspace) {
                $role = in_array(($pendingInvite['role'] ?? 'member'), ['admin', 'member', 'reader'], true)
                    ? $pendingInvite['role']
                    : 'member';

                $inviteWorkspace->members()->syncWithoutDetaching([
                    $user->id => ['role' => $role],
                ]);

                $user->forceFill([
                    'current_workspace_id' => $inviteWorkspace->id,
                ])->save();
            }
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
