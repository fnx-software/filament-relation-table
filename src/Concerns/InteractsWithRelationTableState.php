<?php

namespace FnxSoftware\FilamentRelationTable\Concerns;

use Closure;

trait InteractsWithRelationTableState
{
    protected string|Closure|null $relationship = null;

    protected array|Closure|bool $paginated = true;

    protected array $uniqueColumns = [];

    public function relationship(string|Closure $relationship): static
    {
        $this->relationship = $relationship;

        return $this;
    }

    public function getRelationshipName(): ?string
    {
        return $this->evaluate($this->relationship) ?? $this->getName();
    }

    public function paginated(array|Closure|bool $paginated = true): static
    {
        $this->paginated = $paginated;

        return $this;
    }

    public function getPaginated(): array|bool
    {
        return $this->evaluate($this->paginated);
    }

    public function noDuplicate(array $columns): static
    {
        return $this->uniqueBy($columns);
    }

    public function uniqueBy(string|array $columns): static
    {
        $this->uniqueColumns = (array) $columns;

        return $this;
    }

    public function getUniqueColumns(): array
    {
        return $this->uniqueColumns;
    }
}
