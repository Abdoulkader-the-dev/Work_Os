<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Item;
use App\Models\User;
use App\Models\Board;
use App\Models\Group;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_create_comment_on_item(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $user->id]);
        $user->update(['current_workspace_id' => $workspace->id]);

        // Add user as member (not just owner)
        $workspace->members()->attach($user->id, ['role' => 'member']);

        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post('/comments', [
            'body' => 'This is a test comment',
            'item_id' => $item->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('comments', [
            'body' => 'This is a test comment',
            'item_id' => $item->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_reader_cannot_create_comment(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $user->update(['current_workspace_id' => $workspace->id]);

        // Add user as reader only
        $workspace->members()->attach($user->id, ['role' => 'reader']);

        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post('/comments', [
            'body' => 'This should fail',
            'item_id' => $item->id,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_user_can_update_own_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/comments/{$comment->id}", [
            'body' => 'Updated comment text',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'body' => 'Updated comment text',
        ]);
    }

    public function test_user_cannot_update_others_comment(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->put("/comments/{$comment->id}", [
            'body' => 'Trying to update',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'body' => $comment->body, // Original body unchanged
        ]);
    }

    public function test_user_can_delete_own_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/comments/{$comment->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_user_cannot_delete_others_comment(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->delete("/comments/{$comment->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    public function test_admin_can_delete_any_comment(): void
    {
        $admin = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $admin->id]);
        $admin->update(['current_workspace_id' => $workspace->id]);

        // Add admin as admin role
        $workspace->members()->attach($admin->id, ['role' => 'admin']);

        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($admin)->delete("/comments/{$comment->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_user_can_view_comments_on_item(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $user->update(['current_workspace_id' => $workspace->id]);

        // Add user as member (reader is fine for viewing)
        $workspace->members()->attach($user->id, ['role' => 'reader']);

        // Create item in this workspace
        $board = Board::factory()->create(['workspace_id' => $workspace->id]);
        $group = Group::factory()->create(['board_id' => $board->id]);
        $item = Item::factory()->create(['group_id' => $group->id]);

        $comments = Comment::factory()->count(3)->create(['item_id' => $item->id]);

        $response = $this->actingAs($user)->get("/items/{$item->id}/comments");

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'comments');
    }
}
