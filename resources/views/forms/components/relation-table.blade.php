<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <livewire:filament-relation-table
        :state-path="$getStatePath()"
        :field-key="$getKey()"
        :parent-livewire-id="$this->getId()"
        wire:key="{{ $getKey() }}.relation-table"
    />
</x-dynamic-component>
