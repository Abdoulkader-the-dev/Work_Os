<?php

namespace Tests\Feature\Permissions;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_member(): void
    {
        // Create data
        $admin = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $admin->id]);
        $member = User::factory()->create();

        // Debug info
        dump('Workspace ID value: ' . $workspace->id);
        dump('Workspace ID type: ' . gettype($workspace->id));
        dump('Full URL: ' . "/workspaces/{$workspace->id}/members");

        // Make request
       $response = $this->actingAs($admin)->post(route('workspaces.members.add', ['workspace' => $workspace->id]), [
            'id' => $member->id,
            'role' => 'member',
        ]);

        // Show response
        dump('Response Status: ' . $response->status());
        dump('Response Content: ' . $response->getContent());

        // Assert
        $response->assertStatus(200);
    }
}
