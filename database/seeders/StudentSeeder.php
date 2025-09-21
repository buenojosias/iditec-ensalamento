<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Student;
use App\Models\Team;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $modules = Module::all();
        $teams = Team::whereDoesntHave('students')->get();

        foreach ($teams as $team) {
            $team->prefix = substr((string) $team->schedule, 0, 3);
            $noTeamModules = $modules->where('id', '<>', $team->module_id)->pluck('id')->toArray();

            $createdStudents = Student::factory()
                ->count($team->students_number)
                ->create([
                    'status' => 'A',
                    'schedule' => $team->prefix,
                    'current_team_id' => $team->id,
                ]);

            foreach ($createdStudents as $student) {
                $student->modules()->attach($team->module_id, [
                    'status' => 'C',
                ]);
                for ($i = 1; $i <= rand(1, 11); $i++) {
                    $grade = rand(20, 100);
                    $frequency = rand(50, 100);
                    $status = ($grade >= 60 && $frequency >= 75) ? 'A' : 'R';
                    $student->modules()->syncWithoutDetaching([
                        $modules[array_rand($noTeamModules)],
                        [
                            'grade' => $grade,
                            'frequency' => $frequency,
                            'status' => $status,
                        ]
                    ]);
                }
            }
        }
    }
}
