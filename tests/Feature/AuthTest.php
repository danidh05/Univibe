<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\FacultySeeder;
use Database\Seeders\MajorSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\UnivristySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register_with_valid_data()
    {
        $this->seed(RolesTableSeeder::class);
        $this->seed(UnivristySeeder::class);
        $this->seed(FacultySeeder::class);
        $this->seed(MajorSeeder::class);


        Event::fake();

        $response = $this->postJson('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => '32130642@students.liu.edu.lb',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'university_id' => 1,
            'major_id' => 2,
            'profile_picture' => null,
            'is_verified' => true,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Verfification Email was sent',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => '1john.doe@students.university.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        // Check if the Registered event was dispatched
        Event::assertDispatched(Registered::class);
    }

    /** @test */
    public function registration_fails_with_invalid_email()
    {
        $this->seed(RolesTableSeeder::class);
        $this->seed(UnivristySeeder::class);
        $this->seed(FacultySeeder::class);

        $this->seed(MajorSeeder::class);
        $response = $this->postJson('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@invalid.com', // invalid email (not student)
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'university_id' => 1,
            'major_id' => 2,
            "role_id" => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    /** @test */
    public function registration_fails_with_invalid_password()
    {
        $this->seed(RolesTableSeeder::class);
        $this->seed(UnivristySeeder::class);
        $this->seed(FacultySeeder::class);

        $this->seed(MajorSeeder::class);
        $response = $this->postJson('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => '1john.doe@students.university.com',
            'password' => 'short', // too short
            'password_confirmation' => 'short',
            'university_id' => 1,
            'major_id' => 2,
            "role_id" => 1,

        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }
}
