<div class="space-y-6">
    @livewire('students.list-imported')
    <div class="w-1/2 space-y-4">
        @if ($team)
            <x-ts-card header="Turma" color="secondary" bordered minimize>
                <p><strong>Código:</strong> {{ $team->id }}</p>
                <p><strong>Módulo:</strong> {{ $team->module->name }}</p>
                <p><strong>Horário:</strong> {{ $team->schedule }}</p>
            </x-ts-card>
        @else
            <x-ts-input label="Código da turma" wire:model="team_id" required />
        @endif
        <x-ts-upload wire:model="file" label="Arquivo (PDF)" accept="application/pdf" />
    </div>
</div>
