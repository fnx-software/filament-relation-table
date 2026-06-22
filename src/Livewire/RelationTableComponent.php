<?php

namespace FnxSoftware\FilamentRelationTable\Livewire;

use Filament\Actions\Action as PageAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Concerns\RestrictsFileUploadsToSchemaComponents;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use FnxSoftware\FilamentRelationTable\Forms\Components\RelationTable;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class RelationTableComponent extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;
    use RestrictsFileUploadsToSchemaComponents;

    public string $statePath;

    public string $parentComponentClass;

    public $parentRecordKey = null;

    public array $records = [];

    public function mount(
        string $statePath,
        string $parentComponentClass,
        $parentRecordKey = null,
        array $initialState = []
    ): void {
        $this->statePath = $statePath;
        $this->parentComponentClass = $parentComponentClass;
        $this->parentRecordKey = $parentRecordKey;

        $this->records = collect($initialState)->mapWithKeys(function ($item, $key) {
            $recordKey = $item['id'] ?? $item['_key'] ?? (string) Str::uuid();
            $item['_key'] = $recordKey;

            return [$recordKey => $item];
        })->toArray();
    }

    public function getTableLabel(): string
    {
        return $this->getParentField()->getLabel() ?? 'records';
    }

    protected function getParentField(): RelationTable
    {
        /** @var HasSchemas $parentComponent */
        $parentComponent = app($this->parentComponentClass);

        if ($this->parentRecordKey && method_exists($parentComponent, 'resolveRecord')) {
            $record = $parentComponent->resolveRecord($this->parentRecordKey);
            $parentComponent->record = $record;
        }

        if (! isset($parentComponent->record) || $parentComponent->record === null) {
            if (method_exists($parentComponent, 'getModel')) {
                $modelClass = $parentComponent->getModel();
                if ($modelClass && class_exists($modelClass)) {
                    $parentComponent->record = new $modelClass;
                }
            }
        }

        if (method_exists($parentComponent, 'bootInteractsWithSchemas')) {
            $parentComponent->bootInteractsWithSchemas();
        }

        $schema = null;

        if (method_exists($parentComponent, 'getSchema')) {
            try {
                $schema = $parentComponent->getSchema('form');
            } catch (\Throwable $e) {
                // Fallback
            }
        }

        if (! $schema && isset($parentComponent->form)) {
            $schema = $parentComponent->form;
        }

        if (! $schema && method_exists($parentComponent, 'form')) {
            $schema = Schema::make($parentComponent);
            $schema = $parentComponent->form($schema);
        }

        if (! $schema) {
            throw new \Exception("Could not retrieve form schema from parent component [{$this->parentComponentClass}].");
        }

        $field = collect($schema->getFlatComponents())
            ->first(function ($component) {
                return $component instanceof RelationTable
                    && $component->getStatePath() === $this->statePath;
            });

        if (! $field) {
            throw new \Exception("Could not find RelationTable field with state path [{$this->statePath}] on parent component [{$this->parentComponentClass}].");
        }

        return $field;
    }

    public function table(Table $table): Table
    {
        $field = $this->getParentField();
        $headerActions = $this->buildTableHeaderActions($field);

        return $table
            ->columns($field->getTableColumns())
            ->filters($field->getFilters())
            ->headerActions($headerActions)
            ->actions($this->buildTableActions($field))
            ->records(function (?int $page = null, ?int $recordsPerPage = null, ?string $search = null, ?string $sortColumn = null, ?string $sortDirection = null, ?array $filters = []) {
                return $this->getCustomRecords($page, $recordsPerPage, $search, $sortColumn, $sortDirection, $filters ?? []);
            });
    }

    protected function buildTableHeaderActions(RelationTable $field): array
    {
        $actions = $field->getHeaderActions();

        if (empty($actions)) {
            $actions = [
                CreateAction::make(),
            ];
        }

        return collect($actions)->map(function ($action) use ($field) {
            if (is_object($action) && method_exists($action, 'getName')) {
                if ($action->getName() === 'create' || $action instanceof CreateAction) {
                    return $this->configureCreateAction($action, $field);
                }
            }

            return $action;
        })->all();
    }

    protected function configureCreateAction($action, RelationTable $field)
    {
        if (method_exists($action, 'successNotificationTitle')) {
            $action->successNotificationTitle('Record created successfully.');
        }

        if (method_exists($action, 'recordTitle')) {
            $action->recordTitle(fn () => null); // Nullify closure to prevent type error
        }

        $label = $field->getLabel() ?? 'records';

        return $action
            ->label('Add to '.strtolower($label))
            ->icon($action->getIcon() ?? 'heroicon-m-plus')
            ->schema($field->getSchema())
            ->action(function (array $data) use ($field) {
                $id = (string) Str::uuid();
                if (array_key_exists('id', $data)) {
                    $data['id'] = $id;
                }
                $data['_key'] = $id;

                $this->validateUniqueness($data, $field);

                $this->records[$id] = $data;
                $this->syncState();

                Notification::make()
                    ->success()
                    ->title('Record created successfully.')
                    ->send();
            });
    }

    protected function validateUniqueness(array $data, RelationTable $field, ?string $ignoreKey = null): void
    {
        $uniqueColumns = $field->getUniqueColumns();

        if (empty($uniqueColumns)) {
            return;
        }

        foreach ($this->records as $key => $existingRecord) {
            if ($ignoreKey && $key === $ignoreKey) {
                continue;
            }

            $matchCount = 0;
            foreach ($uniqueColumns as $column) {
                if (isset($data[$column]) && isset($existingRecord[$column]) && $data[$column] === $existingRecord[$column]) {
                    $matchCount++;
                }
            }

            if ($matchCount === count($uniqueColumns)) {
                $firstCol = $uniqueColumns[0];
                throw ValidationException::withMessages([
                    $firstCol => 'A record with this value already exists.',
                ]);
            }
        }
    }

    protected function syncState(): void
    {
        $state = array_values($this->records);
        $this->dispatch('update-relation-table-state', statePath: $this->statePath, state: $state);
    }

    protected function buildTableActions(RelationTable $field): array
    {
        return collect($field->getActions())->map(function ($action) use ($field) {
            if (is_object($action) && method_exists($action, 'getName')) {
                if ($action->getName() === 'edit' || $action instanceof EditAction) {
                    return $this->configureEditAction($action, $field);
                }
                if ($action->getName() === 'delete' || $action instanceof DeleteAction) {
                    return $this->configureDeleteAction($action, $field);
                }
            }

            return $action;
        })->all();
    }

    protected function getActions(): array
    {
        return [
            $this->createAction(),
        ];
    }

    public function createAction(): PageAction
    {
        $field = $this->getParentField();
        $label = $field->getLabel() ?? 'records';

        return PageAction::make('createAction')
            ->label('Add to '.strtolower($label))
            ->color('gray')
            ->button()
            ->outlined()
            ->schema($field->getSchema())
            ->action(function (array $data) use ($field) {
                $id = (string) Str::uuid();
                if (array_key_exists('id', $data)) {
                    $data['id'] = $id;
                }
                $data['_key'] = $id;

                $this->validateUniqueness($data, $field);

                $this->records[$id] = $data;
                $this->syncState();

                Notification::make()
                    ->success()
                    ->title('Record created successfully.')
                    ->send();
            });
    }

    protected function configureEditAction($action, RelationTable $field)
    {
        if (method_exists($action, 'successNotificationTitle')) {
            $action->successNotificationTitle('Record updated successfully.');
        }

        if (method_exists($action, 'recordTitle')) {
            $action->recordTitle(fn () => null); // Nullify closure to prevent type error
        }

        return $action
            ->schema($field->getSchema())
            ->fillForm(fn (array $record): array => $record)
            ->action(function (array $record, array $data) use ($field) {
                $recordKey = $record['_key'] ?? null;

                if ($recordKey && isset($this->records[$recordKey])) {
                    $this->validateUniqueness($data, $field, $recordKey);

                    $this->records[$recordKey] = array_merge($this->records[$recordKey], $data);
                    $this->records[$recordKey]['_key'] = $recordKey;
                }

                $this->syncState();

                Notification::make()
                    ->success()
                    ->title('Record updated successfully.')
                    ->send();
            });
    }

    protected function configureDeleteAction($action, RelationTable $field)
    {
        if (method_exists($action, 'successNotificationTitle')) {
            $action->successNotificationTitle('Record deleted successfully.');
        }

        if (method_exists($action, 'recordTitle')) {
            $action->recordTitle(fn () => null); // Nullify closure to prevent type error
        }

        return $action
            ->requiresConfirmation()
            ->action(function (array $record) {
                $recordKey = $record['_key'] ?? null;

                if ($recordKey && isset($this->records[$recordKey])) {
                    unset($this->records[$recordKey]);
                }

                $this->syncState();

                Notification::make()
                    ->success()
                    ->title('Record deleted successfully.')
                    ->send();
            });
    }

    protected function getCustomRecords(
        ?int $page,
        ?int $recordsPerPage,
        ?string $search,
        ?string $sortColumn,
        ?string $sortDirection,
        array $filters
    ): LengthAwarePaginator|array {
        $collection = collect($this->records);

        // Filters evaluation
        foreach ($filters as $filterName => $filterData) {
            if (isset($filterData['value']) && $filterData['value'] !== null && $filterData['value'] !== '') {
                $value = $filterData['value'];
                $collection = $collection->filter(function ($record) use ($filterName, $value) {
                    return isset($record[$filterName]) && $record[$filterName] == $value;
                });
            } elseif (isset($filterData['isActive']) && $filterData['isActive'] !== null) {
                $isActive = $filterData['isActive'];
                $collection = $collection->filter(function ($record) use ($filterName, $isActive) {
                    return isset($record[$filterName]) && (bool) $record[$filterName] === (bool) $isActive;
                });
            }
        }

        // Search execution
        if ($search !== null && $search !== '') {
            $search = strtolower($search);
            $searchableColumns = collect($this->getParentField()->getTableColumns())
                ->filter(fn ($column) => method_exists($column, 'isSearchable') && $column->isSearchable())
                ->map(fn ($column) => $column->getName())
                ->toArray();

            if (empty($searchableColumns)) {
                $collection = $collection->filter(function ($record) use ($search) {
                    foreach ($record as $key => $value) {
                        if ($key === '_key') {
                            continue;
                        }
                        if (is_string($value) && str_contains(strtolower($value), $search)) {
                            return true;
                        }
                    }

                    return false;
                });
            } else {
                $collection = $collection->filter(function ($record) use ($search, $searchableColumns) {
                    foreach ($searchableColumns as $col) {
                        if (isset($record[$col]) && is_string($record[$col]) && str_contains(strtolower($record[$col]), $search)) {
                            return true;
                        }
                    }

                    return false;
                });
            }
        }

        // Sorting
        if ($sortColumn && $sortDirection) {
            $collection = $sortDirection === 'desc'
                ? $collection->sortByDesc($sortColumn)
                : $collection->sortBy($sortColumn);
        }

        // Pagination
        $field = $this->getParentField();
        $paginated = $field->getPaginated();

        if ($paginated === false) {
            return $collection->all();
        }

        $perPage = $recordsPerPage ?? (is_array($paginated) ? $paginated[0] : 10);
        $currentPage = $page ?? 1;

        $total = $collection->count();
        $results = $collection->slice(($currentPage - 1) * $perPage, $perPage)->all();

        return new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }

    public function render(): View
    {
        return view('fnx-relation-table::forms.livewire.relation-table-component');
    }
}
