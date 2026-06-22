<?php

namespace FnxSoftware\FilamentRelationTable;

use FnxSoftware\FilamentRelationTable\Livewire\RelationTableComponent;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentRelationTableServiceProvider extends PackageServiceProvider
{
    public static string $name = 'fnx-relation-table';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews();
    }

    public function packageBooted(): void
    {
        Livewire::component('fnx-relation-table-component', RelationTableComponent::class);
    }
}
