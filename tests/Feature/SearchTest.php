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
     * Test search returns expected results.
     */
    public function test_search_returns_expected_results()
    {
        // Authenticate the user
        $user = User::factory()->create();
        $this->actingAs($user);

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

        // Perform the search with 'Harvard' as the query term
        $response = $this->getJson('/api/search?query=Harvard');

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
     * Test search returns error if no query provided.
     */
    public function test_search_returns_error_if_no_query_provided()
    {
        // Authenticate the user
        $user = User::factory()->create();
        $this->actingAs($user);

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
        // Authenticate the user
        $user = User::factory()->create();
        $this->actingAs($user);

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

    /**
     * Test searching by major name.
     */
    public function test_search_by_major_name()
    {
        // Authenticate the user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a mock university
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
            'university_id' => $university->id,
            'major_id' => $major->id
        ]);

        // Perform the search with 'Computer Science' as the query term
        $response = $this->getJson('/api/search?query=Computer Science');

        // Assert that the response has the correct structure and contains the data
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'username' => 'john_doee',  // Check username
                 ])
                 ->assertJsonFragment([
                     'major_name' => 'Computer Science',  // Check major name
                 ]);
    }

    /**
     * Test searching by university name.
     */
    public function test_search_by_university_name()
    {
        // Authenticate the user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a mock university
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
            'university_id' => $university->id,
            'major_id' => $major->id
        ]);

        // Perform the search with 'Harvard' as the query term
        $response = $this->getJson('/api/search?query=Harvard');

        // Assert that the response has the correct structure and contains the data
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'username' => 'john_doee',  // Check username
                 ])
                 ->assertJsonFragment([
                     'university_name' => 'Harvard University',  // Check university name
                 ]);
    }

    /**
     * Test searching by bio.
     */
    public function test_search_by_bio()
    {
        // Authenticate the user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a mock university
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
            'university_id' => $university->id,
            'major_id' => $major->id
        ]);

        // Perform the search with 'Studying' as the query term
        $response = $this->getJson('/api/search?query=Studying');

        // Assert that the response has the correct structure and contains the data
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'username' => 'john_doee',  // Check username
                 ]);
    }

    /**
     * Test searching with partial matches.
     */
    public function test_search_with_partial_matches()
    {
        // Authenticate the user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a mock university
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
            'university_id' => $university->id,
            'major_id' => $major->id
        ]);

        // Perform the search with 'Comp' as the query term
        $response = $this->getJson('/api/search?query=Comp');

        // Assert that the response has the correct structure and contains the data
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'username' => 'john_doee',  // Check username
                 ])
                 ->assertJsonFragment([
                     'major_name' => 'Computer Science',  // Check major name
                 ]);
    }
}