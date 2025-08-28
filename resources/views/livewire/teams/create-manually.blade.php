<div>
    @if ($period === 'current')
        <x-ts-button icon="arrow-down-tray" text="Importar turmas" :href="route('teams.import')" />
    @endif
    <x-ts-button icon="plus" :text="$period === 'current' ? 'Adicionar manualmente' : 'Cadastrar turma'" x-on:click="$modalOpen('add-team-modal')" />
    <x-ts-modal id="add-team-modal" title="Adicionar turma manualmente" size="sm" wire>
        <form id="team-create-form" wire:submit="saveTeam" class="space-y-4">
            <div class="flex gap-4">
                <x-ts-input label="Código" wire:model="code" />
                <x-ts-input label="Horário" wire:model="schedule" />
            </div>
            <div>
                <x-ts-select.native label="Módulo" wire:model="module_id">
                    <option value="">Selecione um módulo</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module->id }}">{{ $module->name }}({{ $module->code }})</option>
                    @endforeach
                    </x-ts-select>
            </div>
            @if ($period === 'current')
                <x-ts-number label="Número de Alunos" wire:model="students_number" min="1" />
            @endif
        </form>
        <x-slot:footer>
            <x-ts-button type="submit" form="team-create-form" text="Salvar" />
        </x-slot>
    </x-ts-modal>
</div>
@script
    <script>
        $wire.on('saved', () => {
            $modalClose('add-team-modal');
        });
    </script>
@endscript
