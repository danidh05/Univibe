<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Instructor;
use App\Models\University;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // $this->call(UnivristySeeder::class);
        // University::factory(5)->create();
        // Instructor::factory(10)->create();
        // Course::factory(20)->create();
        $this->call(InternshipSeeder::class);
    }
}
