<?php

namespace Tests\Feature;

use App\Models\Major;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthTest extends TestCase
{



    public function test_user_cannot_register_with_invalid_email()
    {
        $response = $this->postJson('api/register', [
            'username' => 'testuser',
            'email' => 'test@gmail.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'university_id' => 1,
            'major_id' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123!'),
            'is_verified' => true,
        ]);

        $response = $this->postJson('api/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Login successful',
                'user' => [
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => [
                        'Role_name' => $user->role ? $user->role->Role_name : null,
                    ],
                ],
            ]);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('api/login', [
            'email' => 'wrongemail@example.com',
            'password' => 'WrongPassword!',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Invalid credentials',
            ]);
    }

    public function test_user_cannot_login_if_not_verified()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123!'),
            'is_verified' => false,
        ]);

        $response = $this->postJson('api/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Please verify email to login',
            ]);
    }
}
