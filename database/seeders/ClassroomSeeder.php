<?php

namespace Database\Seeders;

use App\Models\Classroom;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $majors = ['MIPA', 'IPS', 'BHS'];
        $levels = [
            'X' => '10',
            'XI' => '11',
            'XII' => '12'
        ];

        foreach ($majors as $major) {
            foreach ($levels as $levelName => $levelNumber) {
                for ($i = 1; $i <= 3; $i++) {
                    Classroom::create([
                        'name' => "$levelName $major $i",
                        'level' => $levelNumber,
                        'major' => $major,
                    ]);
                }
            }
        }
    }
}
