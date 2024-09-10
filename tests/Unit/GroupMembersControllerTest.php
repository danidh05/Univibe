<?php

namespace Tests\Unit;

use App\Models\GroupChat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupMembersControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_member_success()
    {
        $admin = User::factory()->create(['id' => 1]);
        $userToAdd = User::factory()->create(['id' => 3]);
        $groupChat = GroupChat::factory()->create(['id' => 1, 'owner_id' => 1]);

        $response = $this->actingAs($admin)->postJson('/api/group/members/add', [
            'group_chat_id' => $groupChat->id,
            'user_id' => $userToAdd->id,
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Member added successfully!',
                 ]);
    }

    public function test_add_member_validation_error()
    {
        $response = $this->postJson('/api/group/members/add', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['group_chat_id', 'user_id']);
    }

    public function test_add_member_authorization_error()
    {
        $admin = User::factory()->create(['id' => 1]);
        $nonAdmin = User::factory()->create(['id' => 2]);
        $userToAdd = User::factory()->create(['id' => 3]);
        $groupChat = GroupChat::factory()->create(['id' => 1, 'owner_id' => 1]);

        $response = $this->actingAs($nonAdmin)->postJson('/api/group/members/add', [
            'group_chat_id' => $groupChat->id,
            'user_id' => $userToAdd->id,
        ]);

        $response->assertStatus(403)
                 ->assertJson([
                     'success' => false,
                     'message' => 'You are not authorized to update this group.',
                 ]);
    }

    public function test_add_member_duplicate_error()
    {
        $admin = User::factory()->create(['id' => 1]);
        $userToAdd = User::factory()->create(['id' => 3]);
        $groupChat = GroupChat::factory()->create(['id' => 1, 'owner_id' => 1]);
        $groupChat->members()->attach($userToAdd->id, ['joined_at' => now()]);

        $response = $this->actingAs($admin)->postJson('/api/group/members/add', [
            'group_chat_id' => $groupChat->id,
            'user_id' => $userToAdd->id,
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'message' => 'User is already a member of this group.',
                 ]);
    }

    public function test_remove_member_success()
    {
        $admin = User::factory()->create(['id' => 2]);
        $userToRemove = User::factory()->create(['id' => 3]);
        $groupChat = GroupChat::factory()->create(['id' => 1, 'owner_id' => 2]);
        $groupChat->members()->attach($userToRemove->id, ['joined_at' => now()]);

        $response = $this->actingAs($admin)->postJson('/api/group/members/remove', [
            'group_chat_id' => $groupChat->id,
            'user_id' => $userToRemove->id,
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Member removed successfully!',
                 ]);
    }

    public function test_remove_member_validation_error()
    {
        $response = $this->postJson('/api/group/members/remove', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['group_chat_id', 'user_id']);
    }

    public function test_remove_member_authorization_error()
    {
        $admin = User::factory()->create(['id' => 2]);
        $nonAdmin = User::factory()->create(['id' => 3]);
        $userToRemove = User::factory()->create(['id' => 4]);
        $groupChat = GroupChat::factory()->create(['id' => 1, 'owner_id' => 2]);
        $groupChat->members()->attach($userToRemove->id, ['joined_at' => now()]);

        $response = $this->actingAs($nonAdmin)->postJson('/api/group/members/remove', [
            'group_chat_id' => $groupChat->id,
            'user_id' => $userToRemove->id,
        ]);

        $response->assertStatus(403)
                 ->assertJson([
                     'success' => false,
                     'message' => 'You are not authorized to update this group.',
                 ]);
    }

    public function test_remove_member_cannot_remove_self_as_owner()
    {
        $owner = User::factory()->create(['id' => 2]);
        $groupChat = GroupChat::factory()->create(['id' => 1, 'owner_id' => 2]);

        $response = $this->actingAs($owner)->postJson('/api/group/members/remove', [
            'group_chat_id' => $groupChat->id,
            'user_id' => $owner->id,
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'message' => 'You cannot remove yourself (the group owner) from the group.',
                 ]);
    }

    public function test_remove_member_not_in_group()
    {
        $admin = User::factory()->create(['id' => 2]);
        $userToRemove = User::factory()->create(['id' => 3]);
        $groupChat = GroupChat::factory()->create(['id' => 1, 'owner_id' => 2]);

        $response = $this->actingAs($admin)->postJson('/api/group/members/remove', [
            'group_chat_id' => $groupChat->id,
            'user_id' => $userToRemove->id,
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'message' => 'User is not a member of this group.',
                 ]);
    }
}
