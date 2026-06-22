<div class="relation-table-wrapper">
    @if(empty($records))
        <div class="space-y-4">
            <div
                class="flex items-center justify-center p-6 border border-dashed border-gray-300 dark:border-gray-700 rounded-xl bg-transparent">
                <span class="text-sm font-medium text-gray-400 dark:text-gray-500">
                    No {{ $this->getTableLabel() }}
                </span>
            </div>

            <div class="flex justify-start">
                {{ $this->createAction }}
            </div>
        </div>
    @else
        {{ $this->table }}
    @endif

    <x-filament-actions::modals/>
</div>
