<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_mark_single_notification_as_read(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $user->id]);
        $user->update(['current_workspace_id' => $workspace->id]);
        $workspace->members()->attach($user->id, ['role' => 'member']);

        $notification = Notification::create([
            'type' => 'mention',
            'message' => 'Hello',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('notifications.read', $notification));

        $response->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $user->id]);
        $user->update(['current_workspace_id' => $workspace->id]);
        $workspace->members()->attach($user->id, ['role' => 'member']);

        Notification::create([
            'type' => 'mention',
            'message' => 'First',
            'user_id' => $user->id,
        ]);

        Notification::create([
            'type' => 'assignment',
            'message' => 'Second',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('notifications.read-all'));

        $response->assertRedirect();
        $this->assertDatabaseCount('notifications', 2);
        $this->assertDatabaseMissing('notifications', ['user_id' => $user->id, 'read_at' => null]);
    }

    public function test_mark_all_as_read_only_affects_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $user->id]);
        $user->update(['current_workspace_id' => $workspace->id]);
        $workspace->members()->attach($user->id, ['role' => 'member']);
        $workspace->members()->attach($otherUser->id, ['role' => 'member']);

        Notification::create([
            'type' => 'mention',
            'message' => 'User A',
            'user_id' => $user->id,
        ]);

        Notification::create([
            'type' => 'mention',
            'message' => 'User B',
            'user_id' => $otherUser->id,
        ]);

        $this->actingAs($user)
            ->post(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertNotNull(Notification::where('user_id', $user->id)->where('message', 'User A')->firstOrFail()->read_at);
        $this->assertNull(Notification::where('user_id', $otherUser->id)->where('message', 'User B')->firstOrFail()->read_at);
    }

    public function test_user_cannot_mark_other_users_notification_as_read(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $user->id]);
        $user->update(['current_workspace_id' => $workspace->id]);
        $workspace->members()->attach($user->id, ['role' => 'member']);

        $notification = Notification::create([
            'type' => 'mention',
            'message' => 'Private',
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)->post(route('notifications.read', $notification));

        $response->assertForbidden();
        $this->assertNull($notification->fresh()->read_at);
    }
}
