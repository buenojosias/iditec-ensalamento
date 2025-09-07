<?php

namespace App\Livewire\Students;

use App\Models\Student;
use App\Models\Team;
use App\Services\ScheduleService;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

class ListImported extends Component
{
    use Interactions;

    public $teamId;
    public $students = [];
    public $quantityPassed;
    public $missing = [];
    public $showStudents = false;
    public $selectedStudents = [];
    public bool $selectAll = false;
    public $savedStudents = [];

    #[On('extracted')]
    public function handleExtracted($data)
    {
        $this->teamId = $data['team_id'];
        $this->students = $data['students'] ?? [];
        $this->quantityPassed = $data['quantity_passed'];
        $this->missing = $data['missing_ids'] ?? [];
        $this->showStudents = true;
    }

    public function render()
    {
        return view('livewire.students.list-imported');
    }

    public function updatedSelectAll($value)
    {
        $this->selectedStudents  = $value ? array_column($this->students, 'id') : [];
    }

    public function saveStudents()
    {
        $students = array_filter($this->students, fn($student) => in_array($student['id'], $this->selectedStudents  ));

        foreach ($students as $student) {
            $student['current_team_id'] = $this->teamId;

            if ($createdStudent = Student::updateOrCreate(['id' => $student['id']], $student)) {
                foreach ($student['modules'] as $module) {
                    if ($module['id']) {
                        $createdStudent->modules()->syncWithoutDetaching([
                            $module['id'] => [
                                'grade' => $module['grade'],
                                'frequency' => $module['frequency'],
                                'situation' => $module['situation'],
                            ],
                        ]);
                    }
                }

                $this->selectedStudents = array_diff($this->selectedStudents, [$student['id']]);

                $key = array_search($student['id'], array_column($this->students, 'id'));
                if ($key !== false) {
                    $this->students[$key]['saved'] = true;
                }

                $this->savedStudents[] = $student;
            }
        }

        if ($this->savedStudents) {
            $this->toast()->success('Alunos salvos com sucesso.')->send();
        } else {
            $this->toast()->info('Nenhum aluno foi salvo.')->send();
        }
        $this->savedStudents = [];
    }
}
