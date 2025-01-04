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
            'id'=>1,
            'major_name' => 'Computer Science',
            'faculty_id' => 1,
        ]);

        Major::create([
            'id'=>2,
            'major_name' => 'Electrical Engineering',
            'faculty_id' => 2,
        ]);

        Major::create([
            'id'=>3,

            'major_name' => 'Business Administration',
            'faculty_id' => 3,
        ]);
    }
}