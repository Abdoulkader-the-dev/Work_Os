<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspacePermissionsTest extends TestCase
{
    use RefreshDatabase;

    private function makeWorkspaceWithRoles(): array
    {
        $admin = User::factory()->create();
        $member = User::factory()->create();
        $reader = User::factory()->create();

        $workspace = Workspace::create([
            'name' => 'Workspace Test',
            'color' => '#0091CD',
            'user_id' => $admin->id,
        ]);

        $workspace->members()->sync([
            $admin->id => ['role' => 'admin'],
            $member->id => ['role' => 'member'],
            $reader->id => ['role' => 'reader'],
        ]);

        foreach ([$admin, $member, $reader] as $user) {
            $user->forceFill(['current_workspace_id' => $workspace->id])->save();
        }

        return [$workspace, $admin, $member, $reader];
    }

    public function test_admin_can_manage_workspace_members_and_member_cannot(): void
    {
        [$workspace, $admin, $member, $reader] = $this->makeWorkspaceWithRoles();

        $this->actingAs($admin)
            ->post(route('workspaces.members.store', $workspace), [
                'email' => $reader->email,
                'role' => 'member',
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($member)
            ->post(route('workspaces.members.store', $workspace), [
                'email' => $admin->email,
                'role' => 'reader',
            ])
            ->assertForbidden();

        $this->actingAs($reader)
            ->post(route('workspaces.members.store', $workspace), [
                'email' => $admin->email,
                'role' => 'reader',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_update_member_role_and_member_cannot(): void
    {
        [$workspace, $admin, $member] = $this->makeWorkspaceWithRoles();

        $this->actingAs($admin)
            ->patch(route('workspaces.members.update', [$workspace, $member]), [
                'role' => 'reader',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('reader', $workspace->members()->whereKey($member->id)->first()->pivot->role);

        $this->actingAs($member)
            ->patch(route('workspaces.members.update', [$workspace, $admin]), [
                'role' => 'reader',
            ])
            ->assertForbidden();
    }

    public function test_reader_cannot_create_board_and_member_can(): void
    {
        [$workspace, $admin, $member, $reader] = $this->makeWorkspaceWithRoles();

        $this->actingAs($member)
            ->post(route('boards.store'), [
                'name' => 'Board membre',
                'color' => '#0091CD',
            ])
            ->assertRedirect();

        $this->actingAs($reader)
            ->post(route('boards.store'), [
                'name' => 'Board lecteur',
                'color' => '#0091CD',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('boards.store'), [
                'name' => 'Board admin',
                'color' => '#0091CD',
            ])
            ->assertRedirect();
    }

    public function test_reader_can_view_workspace_but_cannot_delete_it(): void
    {
        [$workspace, $admin, $member, $reader] = $this->makeWorkspaceWithRoles();

        $this->actingAs($reader)
            ->get(route('members'))
            ->assertOk();

        $this->actingAs($member)
            ->delete(route('workspaces.destroy', $workspace))
            ->assertForbidden();

        $this->actingAs($admin)
            ->delete(route('workspaces.destroy', $workspace))
            ->assertRedirect();
    }
}
