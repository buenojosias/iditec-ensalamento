<div class="space-y-6">
    {{-- <x-ts-upload wire:model="file" /> --}}
    @livewire('teams.list-imported')
    <x-ts-upload wire:model="file" accept="application/pdf" />
</div>
