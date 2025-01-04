<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Aboutus;
use App\Models\AboutusDetails;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase; // Ensures database is reset for each test

    /**
     * Authenticate the test as an admin.
     */
    protected function authenticateAdmin()
    {
        $admin = User::factory()->admin()->create(); // Use the admin state
        $this->actingAs($admin);
    }

    /**
     * Authenticate the test as a non-admin.
     */
    protected function authenticateNonAdmin()
    {
        $user = User::factory()->create(); // Default state for non-admin
        $this->actingAs($user);
    }

    /** @test */
    public function it_can_get_all_about_us_entries_with_details_as_public()
    {
        // Create Aboutus and AboutusDetails entries
        $aboutUs = Aboutus::factory()->create(['title' => 'About Us Title']);
        AboutusDetails::factory()->create([
            'description' => 'Some details about this',
            'about_us_id' => $aboutUs->id
        ]);

        // Make the request without authentication
        $response = $this->getJson('/api/about-us');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'About Us entries retrieved successfully!',
                'data' => [
                    [
                        'title' => 'About Us Title',
                        'details' => [
                            [
                                'description' => 'Some details about this'
                            ]
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_get_single_about_us_with_details_as_public()
    {
        // Create Aboutus and AboutusDetails entries
        $aboutUs = Aboutus::factory()->create(['title' => 'About Us Title']);
        AboutusDetails::factory()->create([
            'description' => 'Some details about this entry',
            'about_us_id' => $aboutUs->id
        ]);

        // Make the request without authentication
        $response = $this->getJson('/api/about-us/' . $aboutUs->id);

        // Assert the response
        $response->assertStatus(200) // Check for 200 OK
            ->assertJson([
                'message' => 'About Us entry retrieved successfully!',
                'data' => [
                    'id' => $aboutUs->id,
                    'title' => 'About Us Title',
                    'details' => [
                        [
                            'description' => 'Some details about this entry',
                            'about_us_id' => $aboutUs->id,
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_create_about_us_title_as_admin()
    {
        $this->authenticateAdmin();

        $response = $this->postJson('/api/about-us/titles', [
            'title' => 'Our Company Mission'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'About Us title created successfully!',
                'data' => [
                    'title' => 'Our Company Mission'
                ]
            ]);

        $this->assertDatabaseHas('aboutus', [
            'title' => 'Our Company Mission'
        ]);
    }

    /** @test */
    public function it_cannot_create_about_us_title_as_non_admin()
    {
        $this->authenticateNonAdmin();

        $response = $this->postJson('/api/about-us/titles', [
            'title' => 'Our Company Mission'
        ]);

        $response->assertStatus(403); // Forbidden for non-admins
    }

    /** @test */
    public function it_can_create_about_us_detail_as_admin()
    {
        $this->authenticateAdmin();

        $aboutUs = Aboutus::factory()->create(['title' => 'Our Mission']);

        $response = $this->postJson('/api/about-us/' . $aboutUs->id . '/details', [
            'description' => 'This is our mission description'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'About Us detail added successfully!',
                'data' => [
                    'description' => 'This is our mission description',
                    'about_us_id' => $aboutUs->id
                ]
            ]);

        $this->assertDatabaseHas('aboutusDetails', [
            'description' => 'This is our mission description',
            'about_us_id' => $aboutUs->id
        ]);
    }

    /** @test */
    public function it_cannot_create_about_us_detail_as_non_admin()
    {
        $this->authenticateNonAdmin();

        $aboutUs = Aboutus::factory()->create(['title' => 'Our Mission']);

        $response = $this->postJson('/api/about-us/' . $aboutUs->id . '/details', [
            'description' => 'This is our mission description'
        ]);

        $response->assertStatus(403); // Forbidden for non-admins
    }

    /** @test */
    public function it_can_update_about_us_title_as_admin()
    {
        $this->authenticateAdmin();

        $aboutUs = Aboutus::factory()->create(['title' => 'Old Title']);

        $response = $this->putJson('/api/about-us/' . $aboutUs->id, [
            'title' => 'New Title'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'About Us updated successfully!',
                'data' => [
                    'title' => 'New Title'
                ]
            ]);

        $this->assertDatabaseHas('aboutus', [
            'id' => $aboutUs->id,
            'title' => 'New Title'
        ]);
    }

    /** @test */
    public function it_cannot_update_about_us_title_as_non_admin()
    {
        $this->authenticateNonAdmin();

        $aboutUs = Aboutus::factory()->create(['title' => 'Old Title']);

        $response = $this->putJson('/api/about-us/' . $aboutUs->id, [
            'title' => 'New Title'
        ]);

        $response->assertStatus(403); // Forbidden for non-admins
    }

    /** @test */
    public function it_can_update_about_us_detail_as_admin()
    {
        $this->authenticateAdmin();

        $aboutUs = Aboutus::factory()->create();
        $aboutUsDetail = AboutusDetails::factory()->create([
            'about_us_id' => $aboutUs->id,
            'description' => 'Old Description'
        ]);

        $response = $this->putJson('/api/about-us/' . $aboutUs->id . '/details', [
            'description' => 'New Description'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'About Us detail updated successfully!',
                'data' => [
                    'description' => 'New Description',
                    'about_us_id' => $aboutUs->id
                ]
            ]);

        $this->assertDatabaseHas('aboutusDetails', [
            'id' => $aboutUsDetail->id,
            'description' => 'New Description',
            'about_us_id' => $aboutUs->id
        ]);
    }

    /** @test */
    public function it_cannot_update_about_us_detail_as_non_admin()
    {
        $this->authenticateNonAdmin();

        $aboutUs = Aboutus::factory()->create();
        $aboutUsDetail = AboutusDetails::factory()->create([
            'about_us_id' => $aboutUs->id,
            'description' => 'Old Description'
        ]);

        $response = $this->putJson('/api/about-us/' . $aboutUs->id . '/details', [
            'description' => 'New Description'
        ]);

        $response->assertStatus(403); // Forbidden for non-admins
    }

    /** @test */
    public function it_returns_404_for_non_existent_about_us_entry()
    {
        $response = $this->getJson('/api/about-us/9999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_404_for_non_existent_about_us_detail()
    {
        // Authenticate as an admin
        $this->authenticateAdmin();
    
        // Create a valid About Us entry
        $aboutUs = Aboutus::factory()->create();
    
        // Attempt to update a non-existent About Us detail
        $response = $this->putJson('/api/about-us/' . $aboutUs->id . '/details', [
            'description' => 'New Description',
        ]);
    
        // Assert that the response is 404 Not Found
        $response->assertStatus(404);
    }

    /** @test */
    public function it_requires_authentication_to_access_admin_only_endpoints()
    {
        $aboutUs = Aboutus::factory()->create();

        $response = $this->postJson('/api/about-us/' . $aboutUs->id . '/details', [
            'description' => 'Test Description'
        ]);

        $response->assertStatus(401); // Unauthenticated
    }
}