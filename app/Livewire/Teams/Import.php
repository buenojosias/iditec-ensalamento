<?php

namespace App\Livewire\Teams;

use App\Services\ExtractTeamsService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Http\UploadedFile;
use TallStackUi\Traits\Interactions;

class Import extends Component
{
    use Interactions;
    use WithFileUploads;

    public $file;

    public function updatedFile($file)
    {
        $this->validate([
            'file' => 'required|mimes:pdf',
        ]);

        if ($uploadedFile = $this->file->store('imports')) {
            $this->toast()->success('Arquivo enviado com sucesso.', 'Processando as informações.')->send();
            $extractedData = ExtractTeamsService::extract($uploadedFile);

            if (!$extractedData['teams']) {
                $this->toast()->error('Nenhuma turma encontrada.')->send();
            } else {
                $this->toast()->success('Importação concluída.')->send();
                $this->dispatch('extracted', $extractedData);
            }
        } else {
            $this->dialog()->error('Erro ao fazer upload do arquivo.')->send();
        }
    }

    public function render()
    {
        return view('livewire.teams.import')
            ->title('Importar turmas');
    }
}
