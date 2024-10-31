<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Aboutus;
use App\Models\AboutusDetails;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase; // Ensures database is reset for each test

    /** @test */
    /** @test */
    /** @test */
    /** @test */
    /** @test */
    /** @test */
    public function it_can_get_single_about_us_with_details()
    {
        // Create Aboutus and AboutusDetails entries
        $aboutUs = Aboutus::factory()->create(['title' => 'About Us Title']);
        AboutusDetails::factory()->create([
            'description' => 'Some details about this entry',
            'about_us_id' => $aboutUs->id
        ]);

        // Make the request to get the single About Us entry
        $response = $this->getJson('/api/getsingleAboutUsWithDetails/' . $aboutUs->id);

        // Assert the response
        $response->assertStatus(200) // Check for 200 OK
            ->assertJson([
                'data' => [
                    [
                        'id' => $response->json('data.0.id'), // Use the actual ID from the response
                        'description' => 'Some details about this entry',
                        'about_us_id' => $aboutUs->id, // Include about_us_id
                        'created_at' => $response->json('data.0.created_at'), // Check created_at if needed
                        'updated_at' => $response->json('data.0.updated_at'), // Check updated_at if needed
                    ]
                ]
            ]);
    }


    /** @test */
    /** @test */
    public function it_can_create_about_us_detail()
    {
        // Create an Aboutus entry first
        $aboutUs = Aboutus::factory()->create(['title' => 'Our Mission']);

        $response = $this->postJson('/api/createAboutUsDetail/' . $aboutUs->id, [
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
    public function it_can_get_all_about_us_entries_with_details()
    {
        // Create Aboutus and AboutusDetails entries
        $aboutUs = Aboutus::factory()->create(['title' => 'About Us Title']);
        AboutusDetails::factory()->create([
            'description' => 'Some details about this',
            'about_us_id' => $aboutUs->id
        ]);

        $response = $this->getJson('/api/getAllAboutUsWithDetails');

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
}