<?php

declare(strict_types=1);

namespace FnxSoftware\FilamentRelationTable;

use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentRelationTablePlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-relation-table';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
