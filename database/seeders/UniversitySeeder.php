<?php

namespace Database\Seeders;

use App\Models\University;
use Illuminate\Database\Seeder;

class UniversitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Explicitly set the ID for the first university
        University::create([
            'id' => 1, // Set the ID to 1
            'university_name' => 'Liu',
            'Location' => 'Saida'
        ]);

        // Other universities do not need explicit IDs
        University::create([
            'id' => 2, // Set the ID to 2
            'university_name' => 'Harvard',
            'Location' => 'Cambridge'
        ]);

        University::create([
            'id'=>3,
            'university_name' => 'Stanford',
            'Location' => 'Stanford'
        ]);
    }
}