<?php

namespace App\Livewire\Modules;

use App\Models\Module;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

class Create extends Component
{
    use Interactions;

    public $code = "";
    public $position = 1;
    public $name = "";
    public $active = true;

    public function render()
    {
        return view('livewire.modules.create');
    }

    public function saveModule()
    {
        $data = $this->validate([
            'code' => 'required|integer|min:200|max:299',
            'position' => 'required|integer|min:0|max:30',
            'name' => 'required|string|max:140',
            'active' => 'boolean',
        ]);

        if ($module = Module::create($data)) {
            $this->reset();
            $this->toast()->success('Módulo adicionado com sucesso.')->send();
            $this->dispatch('saved');
        } else {
            $this->dialog()->error('Erro ao adicionar módulo.')->send();
        }
    }
}
