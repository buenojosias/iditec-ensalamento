<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Next extends Component
{
    #[Computed('teams')]
    public function getTeamsProperty()
    {
        $teams = Team::where('period', 'next')->with('module')->get();
        $teams->map(function ($team) {
            $team->prefix = substr($team->schedule, 0, 3);
            return $team;
        });

        return $teams;
    }

    public function render()
    {
        return view('livewire.teams.next')
            ->title('Turmas');
    }
}
