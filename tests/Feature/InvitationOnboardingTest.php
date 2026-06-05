<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class InvitationOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_invite_link_stores_pending_invite_and_redirects_to_register(): void
    {
        $workspace = Workspace::factory()->create();

        $url = URL::temporarySignedRoute(
            'workspaces.members.invite',
            now()->addMinutes(5),
            [
                'workspace' => $workspace->id,
                'role' => 'reader',
            ]
        );

        $response = $this->get($url);

        $response->assertRedirect(route('register'));
        $response->assertSessionHas('status', 'workspace-invite-pending');
        $response->assertSessionHas('pending_workspace_invite', function (array $invite) use ($workspace) {
            return (int) $invite['workspace_id'] === (int) $workspace->id
                && $invite['role'] === 'reader';
        });
    }

    public function test_authenticated_user_accepts_signed_invite_link(): void
    {
        $workspace = Workspace::factory()->create();
        $user = User::factory()->create();

        $url = URL::temporarySignedRoute(
            'workspaces.members.invite',
            now()->addMinutes(5),
            [
                'workspace' => $workspace->id,
                'role' => 'member',
            ]
        );

        $response = $this->actingAs($user)->get($url);

        $response->assertRedirect(route('members'));
        $this->assertTrue($workspace->members()->whereKey($user->id)->exists());
        $this->assertSame('member', $workspace->members()->whereKey($user->id)->first()->pivot->role);
        $this->assertSame($workspace->id, $user->fresh()->current_workspace_id);
    }

    public function test_registration_with_pending_workspace_invite_joins_workspace(): void
    {
        $workspace = Workspace::factory()->create();

        $response = $this->withSession([
            'pending_workspace_invite' => [
                'workspace_id' => $workspace->id,
                'role' => 'reader',
            ],
        ])->from(route('register'))->post(route('register'), [
            'name' => 'Invited User',
            'email' => 'invited@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'invited@example.com')->firstOrFail();

        $this->assertSame($workspace->id, $user->current_workspace_id);
        $this->assertTrue($workspace->members()->whereKey($user->id)->exists());
        $this->assertSame('reader', $workspace->members()->whereKey($user->id)->first()->pivot->role);
    }

    public function test_login_with_pending_workspace_invite_joins_workspace(): void
    {
        $workspace = Workspace::factory()->create();
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->from(route('login'))->withSession([
            'pending_workspace_invite' => [
                'workspace_id' => $workspace->id,
                'role' => 'admin',
            ],
        ])->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('members'));

        $user->refresh();
        $this->assertSame($workspace->id, $user->current_workspace_id);
        $this->assertTrue($workspace->members()->whereKey($user->id)->exists());
        $this->assertSame('admin', $workspace->members()->whereKey($user->id)->first()->pivot->role);
    }

    public function test_onboarding_can_be_completed_after_workspace_creation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('workspaces.store'), [
                'name' => 'New Workspace',
            ])
            ->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertTrue($user->shouldShowOnboarding());

        $this->actingAs($user)
            ->post(route('tour.complete'))
            ->assertNoContent();

        $user->refresh();
        $this->assertFalse($user->shouldShowOnboarding());
        $this->assertFalse((bool) ($user->onboarding_state['active'] ?? true));
        $this->assertNotNull($user->onboarding_completed_at);
    }

    public function test_onboarding_storage_key_is_scoped_to_the_user_and_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $otherWorkspace = Workspace::factory()->create();

        $key = $user->onboardingStorageKey($workspace);
        $otherKey = $user->onboardingStorageKey($otherWorkspace);

        $this->assertSame("unipod-onboarding-completed:{$user->id}:{$workspace->id}", $key);
        $this->assertSame("unipod-onboarding-completed:{$user->id}:{$otherWorkspace->id}", $otherKey);
        $this->assertNotSame($key, $otherKey);
    }
}
