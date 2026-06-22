<?php

declare(strict_types=1);

namespace FnxSoftware\FilamentRelationTable\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class RelationTable extends Field
{
    protected string $view = 'filament-relation-table::forms.components.relation-table';

    protected string|Closure|null $relationshipName = null;

    protected array|Closure $schema = [];

    protected array|Closure $tableColumns = [];

    protected array|Closure $filters = [];

    protected array|Closure $headerActions = [];

    protected array $actions = [];

    protected array|bool|Closure $pagination = [10, 25, 50];

    public function relationship(string|Closure $name): static
    {
        $this->relationshipName = $name;

        return $this;
    }

    public function schema(array|Closure $components): static
    {
        $this->schema = $components;

        return $this;
    }

    public function tableColumns(array|Closure $columns): static
    {
        $this->tableColumns = $columns;

        return $this;
    }

    public function filters(array|Closure $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    public function headerActions(array|Closure $actions): static
    {
        $this->headerActions = $actions;

        return $this;
    }

    public function actions(array|Closure $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    public function paginated(array|bool|Closure $pagination): static
    {
        $this->pagination = $pagination;

        return $this;
    }

    public function getRelationshipName(): ?string
    {
        return $this->evaluate($this->relationshipName);
    }

    public function getRelationSchema(): array
    {
        return $this->evaluate($this->schema);
    }

    public function getTableColumns(): array
    {
        return $this->evaluate($this->tableColumns);
    }

    public function getTableFilters(): array
    {
        return $this->evaluate($this->filters);
    }

    public function getHeaderActions(): array
    {
        return $this->evaluate($this->headerActions);
    }

    public function getRecordActions(): array
    {
        return $this->evaluate($this->actions);
    }

    public function getPagination(): array|bool
    {
        return $this->evaluate($this->pagination);
    }
}
