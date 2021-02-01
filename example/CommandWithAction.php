<?php

namespace Example;

use Afeefa\Component\Cli\Command;

class CommandWithAction extends Command
{
    protected function executeCommand()
    {
        $this->runAction(TestAction::class);
    }
}
