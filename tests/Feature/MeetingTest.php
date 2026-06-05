<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Board;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingTest extends TestCase
{
    use RefreshDatabase;

    // ============ CRUD TESTS ============

    public function test_user_can_create_meeting(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $user->id]);
        $user->update(['current_workspace_id' => $workspace->id]);

        // Add user as member
        $workspace->members()->attach($user->id, ['role' => 'member']);

        $data = [
            'title' => 'Team Meeting',
            'date' => '2025-01-15',
        ];

        $response = $this->actingAs($user)->post('/meetings', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('meetings', ['title' => 'Team Meeting']);
    }

    public function test_user_can_read_meeting(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $user->id]);
        $user->update(['current_workspace_id' => $workspace->id]);
        $workspace->members()->attach($user->id, ['role' => 'member']);

        $meeting = Meeting::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Meeting',
            'date' => '2025-01-15'
        ]);

        $response = $this->actingAs($user)->get("/meetings/{$meeting->id}");

        $response->assertStatus(200);
    }

    public function test_user_can_update_meeting(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $user->id]);
        $user->update(['current_workspace_id' => $workspace->id]);
        $workspace->members()->attach($user->id, ['role' => 'member']);

        $meeting = Meeting::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->patch("/meetings/{$meeting->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('meetings', ['id' => $meeting->id, 'title' => 'Updated Title']);
    }

    public function test_user_can_delete_meeting(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $user->id]);
        $user->update(['current_workspace_id' => $workspace->id]);
        $workspace->members()->attach($user->id, ['role' => 'member']);

        $meeting = Meeting::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/meetings/{$meeting->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('meetings', ['id' => $meeting->id]);
    }

    public function test_user_cannot_delete_others_meeting(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $meeting = Meeting::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->delete("/meetings/{$meeting->id}");

        $response->assertStatus(403);
    }

    // ============ ATTENDEE TESTS ============

    public function test_user_can_add_attendee_to_meeting(): void
    {
        $user = User::factory()->create();
        $meeting = Meeting::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post("/meetings/{$meeting->id}/attendees", [
            'name' => 'John Doe'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('meetings', [
            'id' => $meeting->id,
            'attendees' => json_encode(['John Doe'])
        ]);
    }

    public function test_user_can_remove_attendee_from_meeting(): void
    {
        $user = User::factory()->create();
        $meeting = Meeting::factory()->create([
            'user_id' => $user->id,
            'attendees' => ['John Doe', 'Jane Smith']
        ]);

        $response = $this->actingAs($user)->delete("/meetings/{$meeting->id}/attendees/0");

        $response->assertStatus(200);
        $this->assertDatabaseHas('meetings', [
            'id' => $meeting->id,
            'attendees' => json_encode(['Jane Smith'])
        ]);
    }

    // ============ BILAN TESTS ============

    public function test_user_can_add_bilan_point(): void
    {
        $user = User::factory()->create();
        $meeting = Meeting::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post("/meetings/{$meeting->id}/bilan", [
            'point' => 'Completed project milestone'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('meetings', [
            'id' => $meeting->id,
            'bilan' => json_encode(['Completed project milestone'])
        ]);
    }

    public function test_user_can_remove_bilan_point(): void
    {
        $user = User::factory()->create();
        $meeting = Meeting::factory()->create([
            'user_id' => $user->id,
            'bilan' => ['Point A', 'Point B']
        ]);

        $response = $this->actingAs($user)->delete("/meetings/{$meeting->id}/bilan/0");

        $response->assertStatus(200);
        $this->assertDatabaseHas('meetings', [
            'id' => $meeting->id,
            'bilan' => json_encode(['Point B'])
        ]);
    }

    // ============ RECOMMENDATIONS TESTS ============

    public function test_user_can_add_recommendation(): void
    {
        $user = User::factory()->create();
        $meeting = Meeting::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post("/meetings/{$meeting->id}/recommendations", [
            'recommendation' => 'Improve documentation'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('meetings', [
            'id' => $meeting->id,
            'recommendations' => json_encode(['Improve documentation'])
        ]);
    }

    public function test_user_can_remove_recommendation(): void
    {
        $user = User::factory()->create();
        $meeting = Meeting::factory()->create([
            'user_id' => $user->id,
            'recommendations' => ['Rec A', 'Rec B']
        ]);

        $response = $this->actingAs($user)->delete("/meetings/{$meeting->id}/recommendations/0");

        $response->assertStatus(200);
        $this->assertDatabaseHas('meetings', [
            'id' => $meeting->id,
            'recommendations' => json_encode(['Rec B'])
        ]);
    }

    // ============ ACTIONS TESTS ============

    public function test_user_can_add_action(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $user->id]);
        $user->update(['current_workspace_id' => $workspace->id]);
        $meeting = Meeting::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post("/meetings/{$meeting->id}/actions", [
            'text' => 'Review pull requests',
            'assignee_id' => $user->id,
            'deadline' => '2025-01-20'
        ]);

        $response->assertStatus(200);
    }

    public function test_meeting_creation_rejects_action_assignee_outside_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $user->id]);
        $user->update(['current_workspace_id' => $workspace->id]);
        $workspace->members()->attach($user->id, ['role' => 'member']);

        $outsider = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/meetings', [
            'title' => 'Team Meeting',
            'date' => '2025-01-15',
            'actions' => [[
                'text' => 'Review external assignment',
                'assignee_id' => $outsider->id,
                'deadline' => '2025-01-20',
            ]],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['actions.0.assignee_id']);
    }

    public function test_user_can_remove_action(): void
    {
        $user = User::factory()->create();
        $meeting = Meeting::factory()->create([
            'user_id' => $user->id,
            'actions' => [['text' => 'Action 1', 'assignee_id' => null, 'deadline' => null]]
        ]);

        $response = $this->actingAs($user)->delete("/meetings/{$meeting->id}/actions/0");

        $response->assertStatus(200);
    }

    public function test_user_can_convert_action_to_task(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $user->id]);
        $user->update(['current_workspace_id' => $workspace->id]);

        $board = Board::factory()->create(['workspace_id' => $workspace->id]);
        $group = Group::factory()->create(['board_id' => $board->id]);

        $meeting = Meeting::factory()->create([
            'user_id' => $user->id,
            'actions' => [['text' => 'Convert this to task', 'assignee_id' => $user->id, 'deadline' => '2025-01-20', 'converted' => false, 'item_id' => null]]
        ]);

        $response = $this->actingAs($user)->post("/meetings/{$meeting->id}/actions/0/convert");

        $response->assertStatus(200);
        $this->assertDatabaseHas('items', ['name' => 'Convert this to task']);
    }
}
