<?php

namespace Database\Seeders;

use App\Models\Major;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MajorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Major::create([
            'major_name' => 'Computer Science',
            'faculty_id' => 4,
        ]);

        Major::create([
            'major_name' => 'Electrical Engineering',
            'faculty_id' => 5,
        ]);

        Major::create([
            'major_name' => 'Business Administration',
            'faculty_id' => 6,
        ]);
    }
}
