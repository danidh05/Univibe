<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Major;
use App\Models\University;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test no search term provided.
     */
    public function test_search_returns_expected_results()
    {
        // Create a mock university with the name 'Harvard University'
        $university = University::factory()->create([
            'university_name' => 'Harvard University',
            'location' => 'Cambridge, MA'
        ]);

        // Create a mock major
        $major = Major::factory()->create([
            'major_name' => 'Computer Science'
        ]);

        // Create a mock user associated with the university and major
        $user = User::factory()->create([
            'username' => 'john_doee',
            'bio' => 'Studying computer science',
            'university_id' => $university->id,  // Associate the user with the Harvard university
            'major_id' => $major->id              // Associate the user with the major
        ]);

        // Perform the search with 'computer' as the query term
        $response = $this->getJson('/api/search?query=computer');

        // Assert that the response has the correct structure and contains the data
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'username' => 'john_doee',  // Check username
                 ])
                 ->assertJsonFragment([
                     'major_name' => 'Computer Science',  // Check major name
                 ])
                 ->assertJsonFragment([
                     'university_name' => 'Harvard University',  // Check university name
                 ]);
    }



    /**
     * Test no search term provided.
     */
    public function test_search_returns_error_if_no_query_provided()
    {
        // Perform the search with no query term
        $response = $this->getJson('/api/search');

        // Assert that the response contains the correct error message
        $response->assertStatus(400)
                 ->assertJson([
                     'error' => 'No Data Found',
                 ]);
    }

    /**
     * Test search returns empty arrays if no matches are found.
     */
    public function test_search_returns_empty_if_no_results_found()
    {
        // Perform the search with a term that doesn't match any records
        $response = $this->getJson('/api/search?query=nonexistentterm');

        // Assert that the response returns empty arrays for users, majors, and universities
        $response->assertStatus(200)
                 ->assertJson([
                     'users' => [],
                     'majors' => [],
                     'universities' => []
                 ]);
    }
}
