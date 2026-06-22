<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        {{
            $attributes
                ->merge($getExtraAttributes(), escape: false)
                ->class(['fnx-relation-table'])
        }}
    >
        {{-- First version placeholder. --}}
        <div class="fnx-relation-table-placeholder">
            RelationTable field loaded: {{ $getStatePath() }}
        </div>
    </div>
</x-dynamic-component>
