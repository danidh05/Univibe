<?php

namespace Database\Factories;

use App\Models\Major;
use App\Models\Role;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $role = Role::first() ?? Role::factory()->create();
        $major = Major::first() ?? Major::factory()->create();
        $university = University::first() ?? University::factory()->create();

        return [
            'username' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role_id' => $role->id,
            'bio' => 'bio',
            'is_verified' => 1,
            'major_id' => $major->id, // Adding majors_id
            'university_id' => $university->id, // Adding university_id
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
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
