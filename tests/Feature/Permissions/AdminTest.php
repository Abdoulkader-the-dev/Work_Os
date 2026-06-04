<?php

namespace Tests\Feature\Permissions;

use App\Models\User;
use App\Models\Workspace;
use App\Models\Board;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_rename_workspace(): void
    {
        // Create data
        $admin = User::factory()->create();
        $workspace = Workspace::factory()->create();

        // Make the user an admin of the workspace
        $workspace->members()->attach($admin->id, ['role' => 'admin']);

        // New name
        $newName = 'New Workspace Name';

        // Make request
        $response = $this->actingAs($admin)->patch(route('workspaces.update.wk-name', ['workspace' => $workspace->id]), [
            'name' => $newName,
        ]);

        // Assert
        $response->assertStatus(200);

        // Verify name was updated
        $workspace->refresh();
        $this->assertEquals($newName, $workspace->name);
    }

    public function test_admin_can_add_member(): void
    {
        // Create data
        $admin = User::factory()->create();
        $workspace = Workspace::factory()->create();

        // Make the user an admin of the workspace (even though they don't own it)
        $workspace->members()->attach($admin->id, ['role' => 'admin']);

        $member = User::factory()->create();

        // Make request
        $response = $this->actingAs($admin)->post(route('workspaces.members.add', ['workspace' => $workspace->id]), [
            'id' => $member->id,
            'role' => 'member',
        ]);

        // Assert
        $response->assertStatus(200);

        // Verify member was added
        $this->assertTrue($workspace->members()->where('user_id', $member->id)->exists());
    }

       public function test_admin_can_delete_workspace(): void
    {
        // Create data
        $admin = User::factory()->create();
        $workspace = Workspace::factory()->create();

        // Make the user an admin of the workspace
        $workspace->members()->attach($admin->id, ['role' => 'admin']);

        // Make request to delete workspace
        $response = $this->actingAs($admin)->delete(route('workspaces.destroy.wk', ['workspace' => $workspace->id]));

        // Assert
        $response->assertStatus(200);

        // Verify workspace was deleted from database
        $this->assertDatabaseMissing('workspaces', ['id' => $workspace->id]);
    }

    public function test_admin_can_remove_member(): void
    {
        // Create data
        $admin = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $member = User::factory()->create();

        // Make the user an admin of the workspace
        $workspace->members()->attach($admin->id, ['role' => 'admin']);

        // Add the member to workspace
        $workspace->members()->attach($member->id, ['role' => 'member']);

        // Verify member exists before deletion
        $this->assertTrue($workspace->members()->where('user_id', $member->id)->exists());

        // Make request to remove member
        $response = $this->actingAs($admin)->delete(route('workspaces.members.rm-member', [
            'workspace' => $workspace->id,
            'user' => $member->id
        ]));

        // Assert
        $response->assertStatus(200);

        // Verify member was removed
        $this->assertFalse($workspace->members()->where('user_id', $member->id)->exists());
    }

    public function test_admin_can_change_member_role(): void
    {
        // Create data
        $admin = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $member = User::factory()->create();

        // Make the user an admin of the workspace
        $workspace->members()->attach($admin->id, ['role' => 'admin']);

        // Add the member as a regular member
        $workspace->members()->attach($member->id, ['role' => 'member']);

        // Verify member has 'member' role
        $pivot = $workspace->members()->where('user_id', $member->id)->first()->pivot;
        $this->assertEquals('member', $pivot->role);

        // Make request to change role to 'admin'
        $response = $this->actingAs($admin)->patch(route('workspaces.members.change-role', [
            'workspace' => $workspace->id,
            'user' => $member->id
        ]), [
            'role' => 'admin'
        ]);

        // Assert
        $response->assertStatus(200);

        // Verify role was changed to admin
        $workspace->refresh();
        $pivot = $workspace->members()->where('user_id', $member->id)->first()->pivot;
        $this->assertEquals('admin', $pivot->role);
    }

    public function test_admin_cannot_change_workspace_owner_role(): void
    {
        // Create data
        $owner = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $owner->id]);
        $admin = User::factory()->create();

        // Make another user an admin
        $workspace->members()->attach($admin->id, ['role' => 'admin']);

        // Try to change owner's role
        $response = $this->actingAs($admin)->patch(route('workspaces.members.change-role', [
            'workspace' => $workspace->id,
            'user' => $owner->id
        ]), [
            'role' => 'member'
        ]);

        // Assert - should be forbidden
        $response->assertStatus(403);
    }

    public function test_admin_can_create_board(): void
    {
        $admin = User::factory()->create();
        $workspace = Workspace::factory()->create();

        $workspace->members()->attach($admin->id, ['role' => 'admin']);
        $admin->forceFill(['current_workspace_id' => $workspace->id])->save();

        $response = $this->actingAs($admin)->post(route('boards.create'), [
            'name' => 'New Board',
            'color' => '#FF0000',
            'workspace_id' => $workspace->id
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('boards', [
            'name' => 'New Board',
            'color' => '#FF0000',
            'workspace_id' => $workspace->id
        ]);
    }

    public function test_admin_can_edit_board(): void
    {
        $admin = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $board = Board::factory()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Old Name'
        ]);

        $workspace->members()->attach($admin->id, ['role' => 'admin']);

        $response = $this->actingAs($admin)->patch(route('boards.update', ['board' => $board->id]), [
            'name' => 'Updated Board Name'
        ]);

        $response->assertStatus(200);
        $board->refresh();
        $this->assertEquals('Updated Board Name', $board->name);
    }

    public function test_admin_can_delete_board(): void
    {
        $admin = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $board = Board::factory()->create(['workspace_id' => $workspace->id]);

        $workspace->members()->attach($admin->id, ['role' => 'admin']);

        $response = $this->actingAs($admin)->delete(route('boards.delete', ['board' => $board->id]));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('boards', ['id' => $board->id]);
    }

    public function test_non_admin_cannot_create_board(): void
    {
        $member = User::factory()->create();
        $workspace = Workspace::factory()->create();

        $workspace->members()->attach($member->id, ['role' => 'member']);
        $member->forceFill(['current_workspace_id' => $workspace->id])->save();

        $response = $this->actingAs($member)->post(route('boards.create'), [
            'name' => 'New Board',
            'workspace_id' => $workspace->id
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_create_workspace(): void
    {
        // Create a regular user (not admin of anything yet)
        $user = User::factory()->create();

        // Make request to create workspace
        $response = $this->actingAs($user)->post(route('workspaces.store'), [
            'name' => 'My New Workspace'
        ]);

        // Assert - redirects to dashboard
        $response->assertStatus(302);

        // Verify workspace was created
        $this->assertDatabaseHas('workspaces', [
            'name' => 'My New Workspace',
            'user_id' => $user->id
        ]);

        // Verify user is attached as admin to the workspace
        $workspace = Workspace::where('name', 'My New Workspace')->first();
        $this->assertTrue($workspace->members()
            ->where('user_id', $user->id)
            ->where('role', 'admin')
            ->exists());
    }
}
