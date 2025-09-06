<div>
    <div class="header">
        <div class="header-left">
            <h3>Próximas turmas</h3>
            <p>Visão geral das próximas turmas.</p>
        </div>
    </div>
    <div
        class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th width="10">
                        <x-ts-checkbox sm />
                    </th>
                    <th>Código</th>
                    <th>Módulo</th>
                    <th>Prefixo</th>
                    <th>Alunos atribuídos</th>
                    <th width="10"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->teams as $team)
                    <tr>
                        <td>
                            <x-ts-checkbox sm />
                        </td>
                        <td>{{ $team->id }}</td>
                        <td>{{ $team->module->name }} ({{ $team->module->code }})</td>
                        <td>{{ $team->prefix }}</td>
                        <td>
                            <span title="Número apresentado no mapa de turmas">X</span>
                        </td>
                        <td>
                            {{-- <x-ts-button x-on:click="$dispatch('load-module', [{{ $module->id }}])" icon="pencil" text="Editar" sm flat /> --}}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <div class="footer-left">
            <p>Total de turmas: {{ $this->teams->count() }}</p>
        </div>
        <div class="footer-right">
            <livewire:teams.create-manually period="next" @saved="$refresh" />
        </div>
    </div>
    {{-- <livewire:modules.edit @saved="$refresh" /> --}}
</div>
