<?php

namespace FnxSoftware\FilamentRelationTable\Concerns;

use Closure;

trait HasRelationTableSchema
{
    protected array|Closure $schema = [];

    public function schema(array|Closure $schema): static
    {
        $this->schema = $schema;

        return $this;
    }

    public function getSchema(): array
    {
        return $this->evaluate($this->schema);
    }
}
