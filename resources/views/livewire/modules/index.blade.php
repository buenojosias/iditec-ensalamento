<div>
    <div class="header">
        <div class="header-left">
            <h3>Módulos</h3>
            <p>Visão geral dos módulos.</p>
        </div>
        <div class="header-right">
            <x-ts-toggle label="Mostrar apenas ativos" wire:model.live="onlyActive" />
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
                    <th>Nome</th>
                    <th>Código</th>
                    <th>Status</th>
                    <th>Turmas atuais</th>
                    <th>Alunos atuais</th>
                    <th width="10"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->modules as $module)
                    <tr>
                        <td>
                            <x-ts-checkbox sm />
                        </td>
                        <td>{{ $module->name }}</td>
                        <td>{{ $module->code }}</td>
                        <td>
                            <x-ts-badge :text="$module->active ? 'Ativo' : 'Arquivado'" :color="$module->active ? 'green' : 'orange'" round outline />
                        </td>
                        <td>{{ $module->teams_count }}</td>
                        <td>NaN</td>
                        <td>
                            <x-ts-button x-on:click="$dispatch('load-module', [{{ $module->id }}])" icon="pencil" text="Editar" sm flat />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <div class="footer-left">
            <p>Total de módulos: {{ $this->modules->count() }}</p>
        </div>
        <div class="footer-right">
            <livewire:modules.create @saved="$refresh" />
        </div>
    </div>
    <livewire:modules.edit @saved="$refresh" />
</div>
