<?php

namespace Tests\Unit;

use App\Models\GroupChat;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GroupChatControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_group_success()
    {
        $user = User::factory()->create(['id' => 1]); // Ensure user exists
        $this->actingAs($user);

        $photo = UploadedFile::fake()->image('photo.jpg');

        $response = $this->postJson('/api/groups/create', [
            'name' => 'Test Group',
            'photo' => $photo // Optional
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Group created successfully!',
                 ]);

        $this->assertDatabaseHas('group_chats', [
            'group_name' => 'Test Group',
            'owner_id' => 1,
        ]);
    }

    public function test_create_group_validation_error()
    {
        $user = User::factory()->create(['id' => 1]); // Ensure user exists
        $this->actingAs($user);

        $response = $this->postJson('/api/groups/create', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    public function test_get_my_groups_success()
    {
        $user = User::factory()->create(['id' => 1]); // Ensure user exists
        $this->actingAs($user);

        $group = GroupChat::factory()->create();
        $group->members()->attach($user->id, ['joined_at' => now()]);

        $response = $this->getJson('/api/groups/get');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ])
                 ->assertJsonFragment(['group_name' => $group->group_name]);
    }

    public function test_update_group_name_success()
    {
        $user = User::factory()->create(['id' => 1]); // Ensure user exists
        $this->actingAs($user);

        $group = GroupChat::factory()->create(['owner_id' => 1]);

        $response = $this->putJson('/api/groups/update/name', [
            'group_chat_id' => $group->id,
            'name' => 'Updated Group Name',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Group name updated successfully.',
                 ]);

        $this->assertDatabaseHas('group_chats', [
            'id' => $group->id,
            'group_name' => 'Updated Group Name',
        ]);
    }

    public function test_update_group_name_unauthorized()
    {
        $admin = User::factory()->create(['id' => 1]);
        $user = User::factory()->create(['id' => 2]); // Ensure user exists
        $this->actingAs($user);

        $group = GroupChat::factory()->create(['owner_id' => 1]);

        $response = $this->putJson('/api/groups/update/name', [
            'group_chat_id' => $group->id,
            'name' => 'Updated Group Name',
        ]);

        $response->assertStatus(403)
                 ->assertJson([
                     'success' => false,
                     'message' => 'You are not authorized to update this group.',
                 ]);
    }

    public function test_update_group_name_validation_error()
    {
        $user = User::factory()->create(['id' => 1]); // Ensure user exists
        $this->actingAs($user);

        $response = $this->putJson('/api/groups/update/name', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['group_chat_id']);
    }

    public function test_update_group_photo_success()
    {
        Storage::fake('public');
    
        $user = User::factory()->create(['id' => 1]); // Ensure user exists
        $this->actingAs($user);
    
        $group = GroupChat::factory()->create(['owner_id' => 1]);
    
        $response = $this->putJson('/api/groups/update/photo', [
            'group_chat_id' => $group->id,
            'photo' => UploadedFile::fake()->image('photo.jpg'),
        ]);

        Log::info('Response:', $response->json());
    
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Group photo updated successfully.',
                 ]);
    
        // Check if the photo path in the response matches the stored path
        $photoPath = $response->json('group.photo');
        Storage::disk('public')->assertExists($photoPath);
    }

    public function test_update_group_photo_validation_error()
    {
        $user = User::factory()->create(['id' => 1]); // Ensure user exists
        $this->actingAs($user);

        $response = $this->putJson('/api/groups/update/photo', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['group_chat_id']);
    }

    public function test_update_group_photo_unauthorized()
    {
        $admin = User::factory()->create(['id' => 1]);
        $user = User::factory()->create(['id' => 2]); // Ensure user exists
        $this->actingAs($user);

        $group = GroupChat::factory()->create(['owner_id' => 1]);

        $response = $this->putJson('/api/groups/update/photo', [
            'group_chat_id' => $group->id,
            'photo' => UploadedFile::fake()->image('photo.jpg'),
        ]);

        $response->assertStatus(403)
                 ->assertJson([
                     'success' => false,
                     'message' => 'You are not authorized to update this group.',
                 ]);
    }

    public function test_delete_group_success()
    {
        $user = User::factory()->create(['id' => 1]); // Ensure user exists
        $this->actingAs($user);

        $group = GroupChat::factory()->create(['owner_id' => 1]);

        $response = $this->deleteJson('/api/groups/delete', [
            'group_chat_id' => $group->id,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Group chat deleted successfully.',
                 ]);

        $this->assertDatabaseMissing('group_chats', [
            'id' => $group->id,
        ]);
    }

    public function test_delete_group_unauthorized()
    {
        $admin = User::factory()->create(['id' => 1]);
        $user = User::factory()->create(['id' => 2]); // Ensure user exists
        $this->actingAs($user);

        $group = GroupChat::factory()->create(['owner_id' => 1]);

        $response = $this->deleteJson('/api/groups/delete', [
            'group_chat_id' => $group->id,
        ]);

        $response->assertStatus(403)
                 ->assertJson([
                     'success' => false,
                     'message' => 'You are not authorized to update this group.',
                 ]);
    }

    public function test_delete_group_validation_error()
    {
        $user = User::factory()->create(['id' => 1]); // Ensure user exists
        $this->actingAs($user);

        $response = $this->deleteJson('/api/groups/delete', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['group_chat_id']);
    }
}
