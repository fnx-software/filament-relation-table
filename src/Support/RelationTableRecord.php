<?php

declare(strict_types=1);

namespace FnxSoftware\FilamentRelationTable\Support;

class RelationTableRecord
{
    public function __construct(
        public string|int $key,
        public array $data,
    ) {}

    public function toArray(): array
    {
        return [
            '_key' => $this->key,
            ...$this->data,
        ];
    }
}
