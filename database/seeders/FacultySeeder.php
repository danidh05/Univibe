<?php

namespace Database\Seeders;

use App\Models\Faculty;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FacultySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Faculty::create([
            'id'=>1,
            'faculty_name' => 'Dr. Alice Smith',
            "university_id" => 1,
        ]);

        Faculty::create([
            'id'=>2,
            'faculty_name' => 'Dr. Bob Johnson',
            "university_id" => 2,

        ]);

        Faculty::create([
            "id"=>3,

            'faculty_name' => 'Dr. Carol Williams',
            "university_id" => 1,

        ]);
    }
}