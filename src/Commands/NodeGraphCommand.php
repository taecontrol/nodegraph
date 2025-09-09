<?php

namespace Taecontrol\NodeGraph\Commands;

use Illuminate\Console\Command;

class NodeGraphCommand extends Command
{
    public $signature = 'nodegraph';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
