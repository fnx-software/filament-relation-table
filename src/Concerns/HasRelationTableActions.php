<?php

namespace FnxSoftware\FilamentRelationTable\Concerns;

use Closure;

trait HasRelationTableActions
{
    protected array|Closure $headerActions = [];

    protected array $actions = [];

    public function headerActions(array|Closure $actions): static
    {
        $this->headerActions = $actions;

        return $this;
    }

    public function getHeaderActions(): array
    {
        return $this->evaluate($this->headerActions);
    }

    public function actions(array|Closure $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    public function getActions(): array
    {
        return $this->evaluate($this->actions);
    }
}
