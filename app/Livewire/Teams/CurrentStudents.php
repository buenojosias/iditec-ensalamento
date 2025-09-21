<?php

namespace App\Livewire\Teams;

use App\Models\Module;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CurrentStudents extends Component
{
    public $team;
    public $students = [];

    public function mount($team)
    {
        $this->team = $team;
        $this->students = $this->team->students()->with('modules')->orderBy('name')->get();
    }

    // #[Computed('students')]
    // public function getStudentsProperty()
    // {
    //     return $this->team->students;
    // }

    #[Computed('modules')]
    public function getModulesProperty()
    {
        return Module::orderBy('position')->get();
    }

    public function render()
    {
        return view('livewire.teams.current-students');
    }



}
