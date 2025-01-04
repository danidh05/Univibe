<?php

namespace Database\Factories;

use App\Models\Major;
use App\Models\Role;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Fetch or create a default role (e.g., 'user')
        $role = Role::where('role_name', 'user')->first() ?? Role::factory()->create(['role_name' => 'user']);

        // Fetch or create a default major
        $major = Major::first() ?? Major::factory()->create();

        // Fetch or create a default university
        $university = University::first() ?? University::factory()->create();

        return [
            'username' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // Default password
            'remember_token' => Str::random(10),
            'role_id' => $role->id, // Default to 'user' role
            'bio' => 'bio',
            'is_verified' => 1,
            'major_id' => $major->id, // Adding major_id
            'university_id' => $university->id, // Adding university_id
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * State for creating an admin user.
     */
    public function admin(): static
    {
        return $this->state(function (array $attributes) {
            // Fetch or create the admin role
            $adminRole = Role::where('role_name', 'admin')->first() ?? Role::factory()->create(['role_name' => 'admin']);

            return [
                'role_id' => $adminRole->id, // Set role_id to admin
            ];
        });
    }

    /**
     * Configure the factory to add the pusher_channel after creation.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            // Set the pusher_channel based on the user's ID
            $user->pusher_channel = 'user-' . $user->id;
            $user->save();
        });
    }
}