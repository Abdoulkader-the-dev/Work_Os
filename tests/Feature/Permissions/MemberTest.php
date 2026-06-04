<?php

namespace Tests\Feature\Permissions;

use App\Models\User;
use App\Models\Workspace;
use App\Models\Board;
use App\Models\Group;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberTest extends TestCase
{
    use RefreshDatabase;

    // What member CAN do
    public function test_member_can_view_workspace(): void
    {
        $member = User::factory()->create();
        $workspace = Workspace::factory()->create();

        // Add as regular member
        $workspace->members()->attach($member->id, ['role' => 'member']);
        $member->forceFill(['current_workspace_id' => $workspace->id])->save();

        // Try to view workspace dashboard
        $response = $this->actingAs($member)->get(route('dashboard'));

        // Assert - can access
        $response->assertStatus(200);
    }

    public function test_member_can_create_task_on_board(): void
    {
        $member = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $board = Board::factory()->create(['workspace_id' => $workspace->id]);

        $workspace->members()->attach($member->id, ['role' => 'member']);

        // Create a task/item on the board
        $response = $this->actingAs($member)->post(route('boards.items.store', ['board' => $board->id]), [
            'name' => 'New Task',
            'status' => 'todo'
        ]);

        // Assert - can create task (redirects)
        $response->assertStatus(302);
        $this->assertDatabaseHas('items', ['name' => 'New Task']);
    }

    public function test_member_can_edit_task_on_board(): void
    {
        $member = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $board = Board::factory()->create(['workspace_id' => $workspace->id]);
        $group = $board->groups()->create(['name' => 'Test Group']);
        $item = $group->items()->create(['name' => 'Old Task Name']);

        $workspace->members()->attach($member->id, ['role' => 'member']);

        // Edit the task using the correct route
        $response = $this->actingAs($member)->patch(route('items.update', ['item' => $item->id]), [
            'name' => 'Updated Task Name'
        ]);

        // Assert
        $response->assertStatus(200);
        $item->refresh();
        $this->assertEquals('Updated Task Name', $item->name);
    }

    public function test_member_can_receive_notifications(): void
    {
        $member = User::factory()->create();
        $workspace = Workspace::factory()->create();

        $workspace->members()->attach($member->id, ['role' => 'member']);

        // Check notifications page
        $response = $this->actingAs($member)->get(route('notifications.index'));

        // Assert - can access notifications
        $response->assertStatus(200);
    }

    public function test_member_can_use_board_views(): void
    {
        $member = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $board = Board::factory()->create(['workspace_id' => $workspace->id]);

        $workspace->members()->attach($member->id, ['role' => 'member']);

        // Test different board views
        $tableView = $this->actingAs($member)->get(route('boards.show', ['board' => $board->id]));
        $kanbanView = $this->actingAs($member)->get(route('boards.kanban', ['board' => $board->id]));
        $calendarView = $this->actingAs($member)->get(route('boards.calendar', ['board' => $board->id]));

        $tableView->assertStatus(200);
        $kanbanView->assertStatus(200);
        $calendarView->assertStatus(200);
    }

    // What member CANNOT do
    public function test_member_cannot_rename_workspace(): void
    {
        $member = User::factory()->create();
        $workspace = Workspace::factory()->create(['name' => 'Original Name']);

        $workspace->members()->attach($member->id, ['role' => 'member']);

        $response = $this->actingAs($member)->patch(route('workspaces.update.wk-name', ['workspace' => $workspace->id]), [
            'name' => 'New Name'
        ]);

        $response->assertStatus(403);
        $workspace->refresh();
        $this->assertEquals('Original Name', $workspace->name);
    }

    public function test_member_cannot_delete_workspace(): void
    {
        $member = User::factory()->create();
        $workspace = Workspace::factory()->create();

        $workspace->members()->attach($member->id, ['role' => 'member']);

        $response = $this->actingAs($member)->delete(route('workspaces.destroy.wk', ['workspace' => $workspace->id]));

        $response->assertStatus(403);
        $this->assertDatabaseHas('workspaces', ['id' => $workspace->id]);
    }

    public function test_member_cannot_add_member(): void
    {
        $member = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $newMember = User::factory()->create();

        $workspace->members()->attach($member->id, ['role' => 'member']);

        $response = $this->actingAs($member)->post(route('workspaces.members.add', ['workspace' => $workspace->id]), [
            'id' => $newMember->id,
            'role' => 'member'
        ]);

        $response->assertStatus(403);
        $this->assertFalse($workspace->members()->where('user_id', $newMember->id)->exists());
    }

    public function test_member_cannot_remove_member(): void
    {
        $member = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $otherMember = User::factory()->create();

        $workspace->members()->attach($member->id, ['role' => 'member']);
        $workspace->members()->attach($otherMember->id, ['role' => 'member']);

        $response = $this->actingAs($member)->delete(route('workspaces.members.rm-member', [
            'workspace' => $workspace->id,
            'user' => $otherMember->id
        ]));

        $response->assertStatus(403);
        $this->assertTrue($workspace->members()->where('user_id', $otherMember->id)->exists());
    }

    public function test_member_cannot_change_role(): void
    {
        $member = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $otherMember = User::factory()->create();

        $workspace->members()->attach($member->id, ['role' => 'member']);
        $workspace->members()->attach($otherMember->id, ['role' => 'member']);

        $response = $this->actingAs($member)->patch(route('workspaces.members.change-role', [
            'workspace' => $workspace->id,
            'user' => $otherMember->id
        ]), [
            'role' => 'admin'
        ]);

        $response->assertStatus(403);

        // Verify role didn't change
        $pivot = $workspace->members()->where('user_id', $otherMember->id)->first()->pivot;
        $this->assertEquals('member', $pivot->role);
    }

    public function test_member_cannot_create_board(): void
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
        $this->assertDatabaseMissing('boards', ['name' => 'New Board']);
    }
}
