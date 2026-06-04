<?php

namespace Tests\Feature\Permissions;

use App\Models\User;
use App\Models\Workspace;
use App\Models\Board;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReaderTest extends TestCase
{
    use RefreshDatabase;

    // What reader CAN do
    public function test_reader_can_view_workspace(): void
    {
        $reader = User::factory()->create();
        $workspace = Workspace::factory()->create();

        // Add as reader
        $workspace->members()->attach($reader->id, ['role' => 'reader']);
        $reader->forceFill(['current_workspace_id' => $workspace->id])->save();

        // Try to view workspace dashboard
        $response = $this->actingAs($reader)->get(route('dashboard'));

        $response->assertStatus(200);
    }

    public function test_reader_can_view_boards(): void
    {
        $reader = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $board = Board::factory()->create(['workspace_id' => $workspace->id]);

        $workspace->members()->attach($reader->id, ['role' => 'reader']);

        // Try to view board
        $response = $this->actingAs($reader)->get(route('boards.show', ['board' => $board->id]));

        $response->assertStatus(200);
    }

    public function test_reader_can_view_tasks(): void
    {
        $reader = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $board = Board::factory()->create(['workspace_id' => $workspace->id]);
        $group = $board->groups()->create(['name' => 'Test Group']);
        $item = $group->items()->create(['name' => 'Test Task']);

        $workspace->members()->attach($reader->id, ['role' => 'reader']);

        // View task through the board (not separate item route)
        $response = $this->actingAs($reader)->get(route('boards.show', ['board' => $board->id]));

        $response->assertStatus(200);
    }

    public function test_reader_can_view_notifications(): void
    {
        $reader = User::factory()->create();
        $workspace = Workspace::factory()->create();

        $workspace->members()->attach($reader->id, ['role' => 'reader']);

        // Check notifications page
        $response = $this->actingAs($reader)->get(route('notifications.index'));

        $response->assertStatus(200);
    }

    // What reader CANNOT do - create
    public function test_reader_cannot_create_task(): void
    {
        $reader = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $board = Board::factory()->create(['workspace_id' => $workspace->id]);

        $workspace->members()->attach($reader->id, ['role' => 'reader']);

        $response = $this->actingAs($reader)->post(route('boards.items.store', ['board' => $board->id]), [
            'name' => 'New Task'
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('items', ['name' => 'New Task']);
    }

    public function test_reader_cannot_edit_task(): void
    {
        $reader = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $board = Board::factory()->create(['workspace_id' => $workspace->id]);
        $group = $board->groups()->create(['name' => 'Test Group']);
        $item = $group->items()->create(['name' => 'Original Task']);

        $workspace->members()->attach($reader->id, ['role' => 'reader']);

        $response = $this->actingAs($reader)->patch(route('items.update', ['item' => $item->id]), [
            'name' => 'Hacked Task'
        ]);

        $response->assertStatus(403);
        $item->refresh();
        $this->assertEquals('Original Task', $item->name);
    }

    public function test_reader_cannot_delete_task(): void
    {
        $reader = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $board = Board::factory()->create(['workspace_id' => $workspace->id]);
        $group = $board->groups()->create(['name' => 'Test Group']);
        $item = $group->items()->create(['name' => 'Task to Delete']);

        $workspace->members()->attach($reader->id, ['role' => 'reader']);

        // Try to delete - should fail (only admins can delete)
        $response = $this->actingAs($reader)->delete(route('items.destroy', ['item' => $item->id]));

        $response->assertStatus(403);
        $this->assertDatabaseHas('items', ['id' => $item->id]);
    }

    // What reader CANNOT do - workspace management
    public function test_reader_cannot_rename_workspace(): void
    {
        $reader = User::factory()->create();
        $workspace = Workspace::factory()->create(['name' => 'Original Name']);

        $workspace->members()->attach($reader->id, ['role' => 'reader']);

        $response = $this->actingAs($reader)->patch(route('workspaces.update.wk-name', ['workspace' => $workspace->id]), [
            'name' => 'New Name'
        ]);

        $response->assertStatus(403);
        $workspace->refresh();
        $this->assertEquals('Original Name', $workspace->name);
    }

    public function test_reader_cannot_delete_workspace(): void
    {
        $reader = User::factory()->create();
        $workspace = Workspace::factory()->create();

        $workspace->members()->attach($reader->id, ['role' => 'reader']);

        $response = $this->actingAs($reader)->delete(route('workspaces.destroy.wk', ['workspace' => $workspace->id]));

        $response->assertStatus(403);
        $this->assertDatabaseHas('workspaces', ['id' => $workspace->id]);
    }

    // What reader CANNOT do - member management
    public function test_reader_cannot_add_member(): void
    {
        $reader = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $newMember = User::factory()->create();

        $workspace->members()->attach($reader->id, ['role' => 'reader']);

        $response = $this->actingAs($reader)->post(route('workspaces.members.add', ['workspace' => $workspace->id]), [
            'id' => $newMember->id,
            'role' => 'member'
        ]);

        $response->assertStatus(403);
        $this->assertFalse($workspace->members()->where('user_id', $newMember->id)->exists());
    }

    public function test_reader_cannot_remove_member(): void
    {
        $reader = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $otherMember = User::factory()->create();

        $workspace->members()->attach($reader->id, ['role' => 'reader']);
        $workspace->members()->attach($otherMember->id, ['role' => 'member']);

        $response = $this->actingAs($reader)->delete(route('workspaces.members.rm-member', [
            'workspace' => $workspace->id,
            'user' => $otherMember->id
        ]));

        $response->assertStatus(403);
        $this->assertTrue($workspace->members()->where('user_id', $otherMember->id)->exists());
    }

    public function test_reader_cannot_change_role(): void
    {
        $reader = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $otherMember = User::factory()->create();

        $workspace->members()->attach($reader->id, ['role' => 'reader']);
        $workspace->members()->attach($otherMember->id, ['role' => 'member']);

        $response = $this->actingAs($reader)->patch(route('workspaces.members.change-role', [
            'workspace' => $workspace->id,
            'user' => $otherMember->id
        ]), [
            'role' => 'admin'
        ]);

        $response->assertStatus(403);

        $pivot = $workspace->members()->where('user_id', $otherMember->id)->first()->pivot;
        $this->assertEquals('member', $pivot->role);
    }

    // What reader CANNOT do - board management
    public function test_reader_cannot_create_board(): void
    {
        $reader = User::factory()->create();
        $workspace = Workspace::factory()->create();

        $workspace->members()->attach($reader->id, ['role' => 'reader']);
        $reader->forceFill(['current_workspace_id' => $workspace->id])->save();

        $response = $this->actingAs($reader)->post(route('boards.create'), [
            'name' => 'New Board',
            'workspace_id' => $workspace->id
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('boards', ['name' => 'New Board']);
    }

    public function test_reader_cannot_edit_board(): void
    {
        $reader = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $board = Board::factory()->create(['workspace_id' => $workspace->id, 'name' => 'Original Board']);

        $workspace->members()->attach($reader->id, ['role' => 'reader']);

        $response = $this->actingAs($reader)->patch(route('boards.update', ['board' => $board->id]), [
            'name' => 'Hacked Board'
        ]);

        $response->assertStatus(403);
        $board->refresh();
        $this->assertEquals('Original Board', $board->name);
    }

    public function test_reader_cannot_delete_board(): void
    {
        $reader = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $board = Board::factory()->create(['workspace_id' => $workspace->id]);

        $workspace->members()->attach($reader->id, ['role' => 'reader']);

        $response = $this->actingAs($reader)->delete(route('boards.delete', ['board' => $board->id]));

        $response->assertStatus(403);
        $this->assertDatabaseHas('boards', ['id' => $board->id]);
    }
}
