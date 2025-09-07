<?php

namespace App\Livewire\Modules;

use App\Models\Module;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public $onlyActive = false;

    #[Computed('modules')]
    public function getModulesProperty()
    {
        return Module::orderBy('name')
            ->when($this->onlyActive, fn($query) => $query->where('active', true))
            ->withCount('teams')
            // with count of students where pivot is like 'C'
            ->withCount(['students' => fn($query) => $query->where('situation', 'C')])
            ->get();
    }

    public function render()
    {
        return view('livewire.modules.index')
            ->title('Módulos');
    }
}
