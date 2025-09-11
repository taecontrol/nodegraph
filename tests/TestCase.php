<?php

namespace Taecontrol\NodeGraph\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Taecontrol\NodeGraph\NodeGraphServiceProvider;
use Taecontrol\NodeGraph\Tests\Fixtures\SampleState;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Taecontrol\\NodeGraph\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            NodeGraphServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        // Use the test enum for state casting during tests
        config()->set('nodegraph.state_enum', SampleState::class);

        // Run package migration stub directly
        $migration = include __DIR__.'/../database/migrations/create_nodegraph_table.php.stub';
        $migration->up();
    }
}
