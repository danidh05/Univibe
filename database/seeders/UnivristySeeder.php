<?php

namespace Database\Seeders;

use App\Models\University;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnivristySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        University::create([
            'university_name' => 'Liu',
            'Location' => 'Saida'
        ]);

        University::create([
            'university_name' => 'Harvard',
            'Location' => 'Cambridge'
        ]);

        University::create([
            'university_name' => 'Stanford',
            'Location' => 'Stanford'
        ]);
    }
}
