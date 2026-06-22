<?php

namespace FnxSoftware\FilamentRelationTable\Concerns;

use Closure;

trait HasRelationTableColumns
{
    protected array|Closure $tableColumns = [];

    public function tableColumns(array|Closure $columns): static
    {
        $this->tableColumns = $columns;

        return $this;
    }

    public function getTableColumns(): array
    {
        return $this->evaluate($this->tableColumns);
    }
}
