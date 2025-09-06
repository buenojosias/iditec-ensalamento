<div class="space-y-6">
    @if ($showTeams)
        @if ($pendingModules)
            <x-ts-alert title="Atenção" color="amber" light class="mb-4">
                <ul class="list-disc pl-5">
                    @foreach ($pendingModules as $module)
                        <li>Não foi possível encontrar o módulo {{ $module['module_name'] }}, relacionado à turma
                            {{ $module['id'] }} ({{ $module['schedule'] }}).</li>
                    @endforeach
                </ul>
            </x-ts-alert>
        @endif
        <div class="header">
            <div class="header-left">
                <h3>Turmas encontradas</h3>
                <p>Selecione as turmas que deseja salvar.</p>
            </div>
        </div>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th width="10">
                            <x-ts-checkbox wire:model.live="selectAll" sm />
                        </th>
                        <th>Código</th>
                        <th>Módulo</th>
                        <th>Prefixo</th>
                        <th>Alunos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($teams as $team)
                        <tr class="{{ $team['saved'] ? 'bg-green-50' : '' }}">
                            <td>
                                @if ($team['saved'])
                                    <x-ts-icon name="check" color="green" class="w-5 h-5" />
                                @else
                                    <x-ts-checkbox :id="'team-' . $team['id']" wire:model="selectedTeams" :value="$team['id']" sm />
                                @endif
                            </td>
                            <td>{{ $team['id'] }}</td>
                            <td>{{ $team['module_name'] }}</td>
                            <td>{{ $team['schedule'] }}</td>
                            <td>{{ $team['students_number'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="footer">
            <div class="footer-left">

            </div>
            <div class="footer-right">
                <x-ts-button wire:click="saveTeams" text="Salvar turmas" />
            </div>
        </div>
    @endif
</div>
