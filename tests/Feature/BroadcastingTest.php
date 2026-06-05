<?php

namespace Tests\Feature;

use App\Events\BoardUpdated;
use App\Events\MeetingUpdated;
use App\Events\NotificationSent;
use App\Models\Board;
use App\Models\Meeting;
use App\Models\Notification;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroadcastingTest extends TestCase
{
    use RefreshDatabase;

    public function test_board_updated_broadcasts_to_board_and_workspace_channels(): void
    {
        $workspace = Workspace::factory()->create();
        $board = Board::factory()->create(['workspace_id' => $workspace->id]);

        $event = new BoardUpdated($board, 'item.created', ['item_id' => 1]);
        $channels = $event->broadcastOn();

        $this->assertCount(2, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertInstanceOf(PrivateChannel::class, $channels[1]);
        $this->assertSame("private-boards.{$board->id}", $channels[0]->name);
        $this->assertSame("private-workspaces.{$workspace->id}", $channels[1]->name);
        $this->assertSame('BoardUpdated', $event->broadcastAs());
        $this->assertSame([
            'board_id' => $board->id,
            'workspace_id' => $workspace->id,
            'action' => 'item.created',
            'payload' => ['item_id' => 1],
        ], $event->broadcastWith());
    }

    public function test_meeting_updated_broadcasts_only_when_workspace_exists(): void
    {
        $meeting = Meeting::factory()->create(['workspace_id' => null]);
        $event = new MeetingUpdated($meeting, 'updated');

        $this->assertSame([], $event->broadcastOn());

        $workspace = Workspace::factory()->create();
        $meeting = Meeting::factory()->create(['workspace_id' => $workspace->id]);
        $event = new MeetingUpdated($meeting, 'updated');
        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertSame("private-workspaces.{$workspace->id}", $channels[0]->name);
        $this->assertSame([
            'meeting_id' => $meeting->id,
            'action' => 'updated',
            'workspace_id' => $workspace->id,
        ], $event->broadcastWith());
    }

    public function test_notification_sent_broadcasts_to_user_channel(): void
    {
        $user = User::factory()->create();
        $notification = Notification::create([
            'type' => 'mention',
            'message' => 'You were mentioned',
            'user_id' => $user->id,
        ]);

        $event = new NotificationSent($notification);
        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertSame("private-users.{$user->id}", $channels[0]->name);
        $this->assertSame('NotificationSent', $event->broadcastAs());
        $this->assertSame([
            'id' => $notification->id,
            'type' => 'mention',
            'message' => 'You were mentioned',
            'action_url' => null,
            'action_label' => null,
            'created_at' => $notification->created_at?->toIso8601String(),
        ], $event->broadcastWith());
    }
}
