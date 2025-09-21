<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use Livewire\Component;

class Show extends Component
{
    public $team;

    public function mount(Team $team)
    {
        $this->team = $team;
        $this->team->load('module');
        $this->team->loadCount('students');
    }

    public function render()
    {
        return view('livewire.teams.show')
            ->title("Turma");
    }
}
