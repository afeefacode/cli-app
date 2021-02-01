<?php

namespace Example;

use Afeefa\Component\Cli\Command;

class LslCommand extends Command
{
    protected function executeCommand()
    {
        $this->runProcess('ls -l --color="auto"');
    }
}
