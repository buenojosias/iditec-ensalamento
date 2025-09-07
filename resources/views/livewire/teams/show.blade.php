<div>
    <x-ts-alert :text="'Alunos importados: ' . $team->students->count() . ' de ' . $team->students_number" light>
        <x-slot:footer>
            <x-ts-button :href="route('students.import', $team)" text="Importar alunos" sm />
        </x-slot>
    </x-ts-alert>
</div>
