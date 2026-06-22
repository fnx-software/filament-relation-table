<?php

namespace FnxSoftware\FilamentRelationTable\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Tables\Columns\Column;
use FnxSoftware\FilamentRelationTable\Concerns\HasRelationTableActions;
use FnxSoftware\FilamentRelationTable\Concerns\HasRelationTableColumns;
use FnxSoftware\FilamentRelationTable\Concerns\HasRelationTableFilters;
use FnxSoftware\FilamentRelationTable\Concerns\HasRelationTableSchema;
use FnxSoftware\FilamentRelationTable\Concerns\InteractsWithRelationTableState;

class RelationTable extends Field
{
    use HasRelationTableActions;
    use HasRelationTableColumns;
    use HasRelationTableFilters;
    use HasRelationTableSchema;
    use InteractsWithRelationTableState;

    protected string $view = 'fnx-relation-table::forms.components.relation-table';

    /**
     * Overrides columns to dynamically detect if table columns or grid layout columns are provided.
     * Matches the signature of Filament\Schemas\Components\Component::columns exactly to comply with PHP strict standards.
     */
    public function columns(\Closure|array|int|null $columns = 2): static
    {
        if (is_array($columns) && collect($columns)->first() instanceof Column) {
            return $this->tableColumns($columns);
        }

        return parent::columns($columns);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpanFull();
    }
}
