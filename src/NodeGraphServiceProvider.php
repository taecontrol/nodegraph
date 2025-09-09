<?php

namespace Taecontrol\NodeGraph;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Taecontrol\NodeGraph\Commands\NodeGraphCommand;

class NodeGraphServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('nodegraph')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_nodegraph_table')
            ->hasCommand(NodeGraphCommand::class);
    }
}
