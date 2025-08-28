<div class="space-y-6">
    {{-- <x-ts-upload wire:model="file" /> --}}
    @livewire('teams.list-imported')
    <x-ts-input wire:model.live="file" type="file" />
</div>
