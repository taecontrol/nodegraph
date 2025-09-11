<?php

namespace Taecontrol\NodeGraph;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class NodeGraphServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('nodegraph')
            ->hasConfigFile()
            ->hasMigration('create_nodegraph_table');
    }
}
