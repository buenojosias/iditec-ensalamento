<?php

namespace App\Livewire\Teams;

use App\Models\Module;
use App\Models\Team;
use App\Services\ScheduleService;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

class CreateManually extends Component
{
    use Interactions;

    public $modules = [];

    public $module_id;
    public $code;
    public $schedule;
    public $weekday;
    public $time;
    public $shift;
    public $students_number;
    public $period;

    public function mount($period)
    {
        $this->period = $period;
        $this->modules = Module::all();
    }

    public function saveTeam()
    {
        $data = $this->validate(
            [
                'code' => 'required|integer|unique:teams,id',
                'module_id' => 'required|integer|exists:modules,id',
                'schedule' => 'required|string|max:7',
                'students_number' => 'nullable|required_if:period,current|integer|min:0',
                'period' => 'required|string|max:255',
            ]
        );
        $data['id'] = (int) $data['code'];
        $data['weekday'] = ScheduleService::getWeekday($data['schedule']);
        $data['time'] = ScheduleService::getTime($data['schedule']);
        $data['shift'] = ScheduleService::getShift($data['schedule']);

        if (!$data['shift']) {
            $this->dialog()->error('Horário inválido.')->send();
            return;
        }

        if (Team::create($data)) {
            $this->reset(['code', 'module_id', 'schedule', 'students_number']);
            $this->toast()->success('Turma adicionada com sucesso.')->send();
            $this->dispatch('saved');
        } else {
            $this->dialog()->error('Erro ao adicionar turma.')->send();
        }
    }

    public function render()
    {
        return view('livewire.teams.create-manually');
    }
}
