<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use App\Services\ScheduleService;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

class ListImported extends Component
{
    use Interactions;

    public $teams = [];
    public $pendingModules = [];
    public $showTeams = false;
    public $selectedTeams = [];
    public bool $selectAll = false;
    public $savedTeams = [];

    #[On('extracted')]
    public function handleExtracted($data)
    {
        $this->teams = array_filter($data['teams'] ?? [], fn($team) => $team['module_id'] !== null);
        $this->pendingModules = $data['pending_modules'] ?? [];
        $this->showTeams = true;
    }

    public function render()
    {
        return view('livewire.teams.list-imported');
    }

    public function updatedSelectAll($value)
    {
        $this->selectedTeams = $value ? array_column($this->teams, 'id') : [];
    }

    public function saveTeams()
    {
        $teams = array_filter($this->teams, fn($team) => in_array($team['id'], $this->selectedTeams));

        foreach ($teams as $team) {
            $team['weekday'] = ScheduleService::getWeekday($team['schedule']);
            $team['time'] = ScheduleService::getTime($team['schedule']);
            $team['shift'] = ScheduleService::getShift($team['schedule']);
            if (Team::updateOrCreate(['id' => $team['id']], $team)) {
                $this->selectedTeams = array_diff($this->selectedTeams, [$team['id']]);

                $key = array_search($team['id'], array_column($this->teams, 'id'));
                if ($key !== false) {
                    $this->teams[$key]['saved'] = true;
                }

                $this->savedTeams[] = $team;
            }
        }

        if ($this->savedTeams) {
            $this->toast()->success('Turmas salvas com sucesso.')->send();
        } else {
            $this->toast()->info('Nenhuma turma foi salva.')->send();
        }
        $this->savedTeams = [];
    }
}
