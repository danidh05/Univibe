<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Aboutus;
use App\Models\AboutusDetails;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_about_us_title()
    {
        $this->withoutMiddleware();

        $response = $this->postJson('api/about-us/titles', [
            'title' => 'Our Mission',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'About Us title created successfully!',
                'data' => ['title' => 'Our Mission']
            ]);

        $this->assertDatabaseHas('aboutus', [
            'title' => 'Our Mission'
        ]);
    }

    /** @test */
    public function it_creates_about_us_detail()
    {
        $this->withoutMiddleware();

        $aboutUs = Aboutus::factory()->create();

        $response = $this->postJson("api/about-us/{$aboutUs->id}/details", [
            'description' => 'We are dedicated to providing the best service.',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'About Us detail added successfully!',
                'data' => ['description' => 'We are dedicated to providing the best service.']
            ]);

        $this->assertDatabaseHas('aboutusDetails', [
            'about_us_id' => $aboutUs->id,
            'description' => 'We are dedicated to providing the best service.'
        ]);
    }

    /** @test */
    public function it_retrieves_all_about_us_with_details()
    {
        $this->withoutMiddleware();

        $aboutUs = Aboutus::factory()->create();
        AboutusDetails::factory()->count(3)->create(['about_us_id' => $aboutUs->id]);

        $response = $this->getJson('api/about-us');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => ['title', 'details' => [['description']]]
                ]
            ]);
    }

    /** @test */
    public function it_retrieves_single_about_us_with_details()
    {
        $this->withoutMiddleware();

        $aboutUs = Aboutus::factory()->create();
        $aboutUsDetail = AboutusDetails::factory()->create(['about_us_id' => $aboutUs->id]);

        $response = $this->getJson("api/about-us/{$aboutUs->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['description' => $aboutUsDetail->description]
                ]
            ]);
    }

    /** @test */
    public function it_updates_about_us_title()
    {
        $this->withoutMiddleware();

        $aboutUs = Aboutus::factory()->create(['title' => 'Initial Title']);

        $response = $this->putJson("api/about-us/{$aboutUs->id}", [
            'title' => 'Updated Title'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'About Us updated successfully!',
                'data' => ['title' => 'Updated Title']
            ]);

        $this->assertDatabaseHas('aboutus', [
            'id' => $aboutUs->id,
            'title' => 'Updated Title'
        ]);
    }

    /** @test */
    public function it_updates_about_us_detail()
    {
        $this->withoutMiddleware();

        $aboutUs = Aboutus::factory()->create();
        $aboutUsDetail = AboutusDetails::factory()->create([
            'about_us_id' => $aboutUs->id,
            'description' => 'Initial Description'
        ]);

        $response = $this->putJson("api/about-us/{$aboutUs->id}/details", [
            'description' => 'Updated Description'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'About Us detail updated successfully!',
                'data' => ['description' => 'Updated Description']
            ]);

        $this->assertDatabaseHas('aboutusDetails', [
            'id' => $aboutUsDetail->id,
            'description' => 'Updated Description'
        ]);
    }
}
