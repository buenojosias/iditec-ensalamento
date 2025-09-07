<div class="space-y-4">
    @if ($showStudents)
        @if ($missing)
            <x-ts-alert title="Atenção" color="amber" light class="mb-4">
                <ul class="list-disc pl-5">
                    @foreach ($missing as $student)
                        <li>Não foi possível importar o aluno {{ $student }}.</li>
                    @endforeach
                </ul>
            </x-ts-alert>
        @elseif ($quantityPassed === false)
            <x-ts-alert title="Atenção" color="amber" light class="mb-4">
                <p>Nem todos os alunos foram importados. Verifique os dados.</p>
            </x-ts-alert>
        @endif
        <div class="header">
            <div class="header-left">
                <h3>Alunos importados</h3>
                <p>Selecione os alunos que deseja salvar.</p>
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
                        <th>Nome</th>
                        <th width="40%">Módulos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $student)
                        <tr class="{{ $student['saved'] ? 'bg-green-50' : '' }}">
                            <td>
                                @if ($student['saved'])
                                    <x-ts-icon name="check" color="green" class="w-5 h-5" />
                                @else
                                    <x-ts-checkbox :id="'student-' . $student['id']" wire:model="selectedStudents" :value="$student['id']"
                                        sm />
                                @endif
                            </td>
                            <td>{{ $student['id'] }}</td>
                            <td>
                                <div class="flex items-center h-auto gap-2">
                                    @if ($student['has_missing_modules'])
                                        <x-ts-tooltip text="Este aluno possui módulos que não foram encontrados."
                                            icon="exclamation-triangle" color="amber" xs />
                                    @endif
                                    {{ $student['name'] }}
                                </div>
                            </td>
                            {{-- <td>{{ $student['modules'] }}</td> --}}
                            <td x-data="{ open: false }">
                                <div @click="open = !open" class="cursor-pointer flex items-center gap-1">
                                    <span class="font-semibold">Módulos</span>
                                    <x-ts-icon name="chevron-down" class="w-4 h-4" x-show="!open" />
                                    <x-ts-icon name="chevron-up" class="w-4 h-4" x-show="open" />
                                </div>
                                <div x-show="open" x-collapse class="mt-2">
                                    @foreach ($student['modules'] as $module)
                                        <div
                                            class="flex items-center gap-2 {{ $module['situation'] === 'C' ? 'font-semibold text-blue-600' : ($module['situation'] === 'R' ? 'text-red-600' : 'text-green-600') }}">
                                            @if ($module['id'] === null)
                                                <x-ts-tooltip text="Este módulo não foi encontrado."
                                                    icon="exclamation-triangle" color="amber" xs />
                                            @endif
                                            {{ $module['name'] }}
                                        </div>
                                        {{-- @dump($module) --}}
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="footer">
            <div class="footer-left">

            </div>
            <div class="footer-right">
                <x-ts-button wire:click="saveStudents" text="Salvar alunos" />
            </div>
        </div>
    @endif
</div>
