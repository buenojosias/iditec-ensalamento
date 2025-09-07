<?php

namespace App\Livewire\Students;

use App\Models\Team;
use App\Services\ExtractStudentsService;
use Livewire\Component;
use Livewire\WithFileUploads;
use TallStackUi\Traits\Interactions;

class Import extends Component
{
    use Interactions;
    use WithFileUploads;

    public $team;

    public $file;
    public $team_id;

    public function mount($team = null)
    {
        if ($team) {
            $this->team = Team::with('module')->findOrFail($team);
            $this->team_id = $team;
        }
    }

    public function render()
    {
        return view('livewire.students.import')
            ->title('Importar alunos');
    }

    public function updatedFile($file)
    {
        $this->validate([
            'team_id' => 'required|exists:teams,id',
            'file' => 'required|mimes:pdf',
        ]);

        if ($uploadedFile = $this->file->store('imports')) {
            $this->toast()->success('Arquivo enviado com sucesso.', 'Processando as informações.')->send();
            $extractedData = ExtractStudentsService::extract($uploadedFile, $this->team_id);

            if (isset($extractedData['error'])) {
                $this->dialog()->error('Erro ao processar o arquivo.', $extractedData['error'])->send();
                return;
            }

            if (!$extractedData['students']) {
                $this->toast()->error('Nenhum aluno encontrado.')->send();
            } else {
                $this->toast()->success('Importação concluída.')->send();
                $this->dispatch('extracted', $extractedData);
            }

            $this->reset('file');
        } else {
            $this->dialog()->error('Erro ao fazer upload do arquivo.')->send();
        }
    }
}
