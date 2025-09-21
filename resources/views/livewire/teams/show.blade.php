<div class="flex gap-6">
    <div class="w-[260px]">
        <x-ts-card header="Informaçõess da turma">
            <div class="space-y-2">
                <div class="flex flex-col">
                    <span class="font-semibold">Horário</span>
                    <span>{{ $team->schedule }}</span>
                </div>
                <div class="flex flex-col">
                    <span class="font-semibold">Módulo</span>
                    <span>{{ $team->module->name }}</span>
                </div>
                <div class="flex flex-col">
                    <span class="font-semibold">Alunos mapeados</span>
                    <span>{{ $team->students_number }}</span>
                </div>
                <div class="flex flex-col">
                    <span class="font-semibold">Alunos importados</span>
                    <span>{{ $team->students_count }}</span>
                </div>
            </div>
        </x-ts-card>
    </div>
    <div class="flex-1">
        @if ($team->period === 'current')
            <livewire:teams.current-students :team="$team" />
        @else
            Listar próxima
        @endif
    </div>
</div>
