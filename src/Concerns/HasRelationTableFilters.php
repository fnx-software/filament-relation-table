<?php

namespace FnxSoftware\FilamentRelationTable\Concerns;

use Closure;

trait HasRelationTableFilters
{
    protected array|Closure $filters = [];

    public function filters(array|Closure $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    public function getFilters(): array
    {
        return $this->evaluate($this->filters);
    }
}
