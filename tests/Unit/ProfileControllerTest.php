<?php

namespace Tests\Unit;

use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Create a user to test with
        $this->user = User::factory()->create([
            'username' => 'testuser',
            'bio' => 'This is my bio',
            'profile_picture' => 'profile_pictures/old_picture.jpg'
        ]);

        // Mock authentication
        $this->actingAs($this->user);
    }

    public function test_it_can_update_profile_picture()
    {
        // Mock the file storage
        Storage::fake('public');

        // Simulate the profile picture update request
        $file = UploadedFile::fake()->image('new_picture.jpg');
        $response = $this->json('POST', '/api/user/update', [
            'profile_picture' => $file,
        ]);

        // Assert the file was uploaded and stored
        Storage::disk('public')->assertExists('profile_pictures/' . $file->hashName());

        // Assert that the old profile picture was deleted
        Storage::disk('public')->assertMissing('profile_pictures/old_picture.jpg');

        // Assert that the user's profile picture was updated
        $this->assertEquals('profile_pictures/' . $file->hashName(), $this->user->fresh()->profile_picture);

        // Assert the response status and message
        $response->assertStatus(200)
            ->assertJson(['message' => 'Profile updated successfully.']);
    }

    public function test_it_can_update_bio_with_profanity_filter()
    {
        // Mock profanity filter service
        $this->app->bind('profanityFilter', function() {
            return new class {
                public function filter($string) {
                    return str_replace('badword', '****', $string);
                }
            };
        });

        $response = $this->json('POST', '/api/user/update', [
            'bio' => 'This is a clean bio',
        ]);

        // Assert the bio was updated
        $this->assertEquals('This is a clean bio', $this->user->fresh()->bio);

        // Assert the response status and message
        $response->assertStatus(200)
            ->assertJson(['message' => 'Profile updated successfully.']);
    }

    public function test_it_filters_profanity_in_bio()
    {
        // Mock the profanity filter service
        $this->app->bind('profanityFilter', function () {
            return new class {
                public function filter($string) {
                    return str_replace('badword', '****', $string);
                }
            };
        });

        $response = $this->json('POST', '/api/user/update', [
            'bio' => 'This contains badword',
        ]);

        // Assert the bio was filtered and updated
        $this->assertEquals('This contains ****', $this->user->fresh()->bio);

        // Assert the response status and message
        $response->assertStatus(200)
            ->assertJson(['message' => 'Profile updated successfully.']);
    }

    public function test_it_can_update_username()
    {
        $response = $this->json('POST', '/api/user/update', [
            'username' => 'newusername',
        ]);

        // Assert the username was updated
        $this->assertEquals('newusername', $this->user->fresh()->username);

        // Assert the response status and message
        $response->assertStatus(200)
            ->assertJson(['message' => 'Profile updated successfully.']);
    }

    public function test_it_returns_error_if_no_changes_made()
    {
        $response = $this->json('POST', '/api/user/update', [
            'username' => 'testuser', // Same username
            'bio' => 'This is my bio', // Same bio
        ]);

        // Assert the response status and error message
        $response->assertStatus(400)
            ->assertJson(['error' => 'No changes detected.']);
    }

    public function test_it_returns_validation_errors_for_zero_inputs()
    {
        $response = $this->json('POST', '/api/user/update', [
        ]);
    
        // Assert the response status and error message
        $response->assertStatus(400)
        ->assertJson(['error' => 'No changes detected.']);
    }    
}
