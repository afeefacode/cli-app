<?php

namespace Example;

use Afeefa\Component\Cli\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CommandWithMode extends Command
{
    protected function setArguments()
    {
        $this
            ->addSelectableArgument(
                'v_direction',
                ['up', 'down'],
                InputArgument::OPTIONAL,
                'The vertical direction'
            )
            ->addArgument(
                'h_direction',
                InputArgument::OPTIONAL,
                'The horizontal direction'
            )
            ->addOption(
                'reset',
                null,
                InputOption::VALUE_NONE,
                'Reset',
                null
            );
    }

    protected function executeCommand()
    {
        $mode = $this->getMode('default');

        $this->printText("The mode is: $mode");
    }
}
