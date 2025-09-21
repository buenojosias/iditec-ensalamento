<div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-10">Código</th>
                    <th>Nome</th>
                    @foreach ($this->modules as $module)
                        <th width="10" title="{{ $module->name }}" class="align-top">
                            {{ $module->code }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $student)
                    <tr>
                        <td>{{ $student->id }}</td>
                        <td>{{ substr($student->name, 0, 30) }}...</td>
                        @foreach ($this->modules as $module)
                            <td class="text-center">
                                @if ($student->modules->contains($module->id))
                                    {{-- <x-ts-icon
                                        :name="$student->modules->find($module->id)->pivot->situation== 'C' ? 'clock' : ($student->modules->find($module->id)->pivot->situation== 'A' ? 'check-circle' : 'x-circle')"
                                        class="h-5 w-5 text-{{ $student->modules->find($module->id)->pivot->situation== 'C' ? 'blue' : ($student->modules->find($module->id)->pivot->situation== 'A' ? 'green' : 'red')  }}-500"
                                    /> --}}
                                    <div
                                        title="{{ \App\Enums\StudentModuleStatusEnum::from($student->modules->find($module->id)->pivot->situation)->getLabel() }}"
                                        class="h-5 w-5 bg-{{ \App\Enums\StudentModuleStatusEnum::from($student->modules->find($module->id)->pivot->situation)->getColor() }}-500 rounded">
                                    </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
