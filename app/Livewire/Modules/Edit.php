<?php

namespace App\Livewire\Modules;

use App\Models\Module;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

class Edit extends Component
{
    use Interactions;

    public $id = "";
    public $code = "";
    public $position = "";
    public $name = "";
    public bool $active;
    public $modal = false;

    #[On('load-module')]
    public function loadModule($id = null)
    {
        $module = Module::find($id);
        if ($module) {
            $this->id = $module->id;
            $this->code = $module->code;
            $this->position = $module->position;
            $this->name = $module->name;
            $this->active = $module->active;
            $this->modal = true;
        }
    }

    public function render()
    {
        return view('livewire.modules.edit');
    }

    public function saveModule()
    {
        $data = $this->validate([
            'code' => 'required|integer|min:200|max:299',
            'position' => 'required|integer|min:1|max:30',
            'name' => 'required|string|max:140',
            'active' => 'boolean',
        ]);

        if (Module::where('id', $this->id)->update($data)) {
            $this->toast()->success('Módulo atualizado com sucesso.')->send();
            $this->dispatch('saved');
            $this->reset();
        } else {
            $this->dialog()->error('Erro ao atualizar módulo.')->send();
        }
    }
}
