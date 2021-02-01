<?php

namespace Example;

use Afeefa\Component\Cli\Command;

class CommandWithMode extends Command
{
    protected function executeCommand()
    {
        $mode = $this->getMode('default');

        $this->printText("The mode is: $mode");
    }
}
