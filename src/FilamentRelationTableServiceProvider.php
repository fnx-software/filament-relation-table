<?php

declare(strict_types=1);

namespace FnxSoftware\FilamentRelationTable;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use FnxSoftware\FilamentRelationTable\Livewire\RelationTableComponent;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentRelationTableServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-relation-table')
            ->hasViews();
    }

    public function packageBooted(): void
    {
        Livewire::component(
            'filament-relation-table',
            RelationTableComponent::class,
        );

        FilamentAsset::register([
            Css::make(
                'filament-relation-table',
                __DIR__.'/../dist/css/relation-table.css',
            ),
        ], 'fnx-software/filament-relation-table');
    }
}
