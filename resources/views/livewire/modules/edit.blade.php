<div>
    <x-ts-modal wire title="Editar Módulo" size="sm">
        <form id="module-edit-form" wire:submit="saveModule" class="space-y-4">
            <div class="flex gap-4 justify-between">
                <div class="w-1/2">
                    <x-ts-input label="Código" wire:model="code" placeholder="2xx" />
                </div>
                <div class="w-1/2">
                    <x-ts-number label="Posição" min="1" wire:model="position" />
                </div>
            </div>
            <x-ts-input label="Nome" wire:model="name" />
            <x-ts-toggle label="Ativo" wire:model="active" />
        </form>
        <x-slot:footer>
            <x-ts-button type="submit" form="module-edit-form" text="Salvar" />
        </x-slot>
    </x-ts-modal>
</div>
