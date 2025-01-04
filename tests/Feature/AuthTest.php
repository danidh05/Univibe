<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\FacultySeeder;
use Database\Seeders\MajorSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\UniversitySeeder; // Corrected typo: UnivristySeeder -> UniversitySeeder
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
{
    parent::setUp();

    // Seed necessary data before each test
    $this->seed(RolesTableSeeder::class);
    $this->seed(UniversitySeeder::class);
    $this->seed(FacultySeeder::class);
    $this->seed(MajorSeeder::class);
}
    /** @test */
    public function user_can_register_with_valid_data()
    {
       

        Event::fake();

        $response = $this->postJson('/api/register', [ // Corrected route to /api/register
            'username' => 'john_doe', // Added username field
            'email' => '32130642@students.liu.edu.lb',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'university_id' => 1,
            'major_id' => 1,
            'profile_picture' => null,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Verification Email was sent', // Corrected typo: Verfification -> Verification
            ]);

        // Check if the user was created in the database
        $this->assertDatabaseHas('users', [
            'email' => '32130642@students.liu.edu.lb', // Corrected email
            'username' => 'john_doe', // Added username field
        ]);

        // Check if the Registered event was dispatched
        Event::assertDispatched(Registered::class);
    }

/** @test */
public function registration_fails_with_invalid_email()
{
  

    $response = $this->postJson('/api/register', [ // Corrected route to /api/register
        'username' => 'john_doe', // Added username field
        'email' => 'john.doe@invalid.com', // invalid email (not student)
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'university_id' => 1,
        'major_id' => 2,
    ]);

    $response->assertStatus(400) // Changed from 422 to 400 (as per controller logic)
        ->assertJsonValidationErrors('email');
}

/** @test */
public function registration_fails_with_invalid_password()
{
  

    $response = $this->postJson('/api/register', [ // Corrected route to /api/register
        'username' => 'john_doe', // Added username field
        'email' => '32130642@students.liu.edu.lb',
        'password' => 'short123123', // too short
        'password_confirmation' => 'short',
        'university_id' => 1,
        'major_id' => 2,
    ]);

    $response->assertStatus(400) // Changed from 422 to 400 (as per controller logic)
        ->assertJsonValidationErrors('password');
}

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!'),
            'is_verified' => true, // User is verified
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user' => [
                    'id',
                    'username',
                    'email',
                    'role',
                ],
            ]);
    }

    /** @test */
    public function login_fails_with_invalid_credentials()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!'),
            'is_verified' => true, // User is verified
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword!', // Invalid password
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Invalid credentials',
            ]);
    }

    /** @test */
    public function login_fails_for_unverified_user()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!'),
            'is_verified' => false, // User is not verified
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Please verify your email to login',
            ]);
    }

    /** @test */
    public function user_can_logout()
    {
        // Create a user and generate a token
        $user = User::factory()->create();
        $token = $user->createToken('main')->plainTextToken;

        // Logout the user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out successfully',
            ]);

        // Check if the token was revoked
        $this->assertCount(0, $user->tokens);
    }
}