<?php

namespace Example;

use Afeefa\Component\Cli\Command;

class SimpleCommand extends Command
{
    protected function executeCommand()
    {
        $this->runProcess('ls -l --color="auto"');

        return 0;
    }
}
