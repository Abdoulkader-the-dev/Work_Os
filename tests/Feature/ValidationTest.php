<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Group;
use App\Models\Meeting;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_creation_rejects_group_from_other_board(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $user->id]);
        $user->update(['current_workspace_id' => $workspace->id]);
        $workspace->members()->attach($user->id, ['role' => 'member']);

        $boardA = Board::factory()->create(['workspace_id' => $workspace->id]);
        $boardB = Board::factory()->create(['workspace_id' => $workspace->id]);
        $foreignGroup = Group::factory()->create(['board_id' => $boardB->id]);

        $response = $this->actingAs($user)->post(route('boards.items.store', $boardA), [
            'name' => 'Invalid task',
            'group_id' => $foreignGroup->id,
        ]);

        $response->assertSessionHasErrors(['group_id']);
        $this->assertDatabaseMissing('items', ['name' => 'Invalid task']);
    }

    public function test_meeting_creation_rejects_action_item_outside_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $user->id]);
        $user->update(['current_workspace_id' => $workspace->id]);
        $workspace->members()->attach($user->id, ['role' => 'member']);

        $otherWorkspace = Workspace::factory()->create();
        $otherBoard = Board::factory()->create(['workspace_id' => $otherWorkspace->id]);
        $otherGroup = Group::factory()->create(['board_id' => $otherBoard->id]);
        $foreignItem = $otherGroup->items()->create(['name' => 'Foreign item']);

        $response = $this->actingAs($user)->postJson(route('meetings.store'), [
            'title' => 'Team Meeting',
            'date' => '2025-01-15',
            'actions' => [[
                'text' => 'Link to foreign task',
                'item_id' => $foreignItem->id,
            ]],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['actions.0.item_id']);
    }
}
