<?php

namespace Afeefa\Component\Cli;

class CommandGroup extends Command
{
    protected function executeCommand()
    {
        /** @var Application */
        $application = $this->getApplication();

        $commands = $application->all();

        $commands = array_filter($commands, function ($command) {
            $name = $this->getName();

            if ($name === 'index') {
                // show root commands if index
                if (preg_match('/:/', $command->getName())) {
                    return false;
                }
            } else {
                // show only commands of the current scope
                if (!preg_match("/^$name/", $command->getName())) {
                    return false;
                }

                // do not show deep nested commands
                if (preg_match("/^$name:.*:/", $command->getName())) {
                    return false;
                }
            }

            // do not show this command group in list
            if ($name === $command->getName()) {
                return false;
            }

            // do not show generic list and help commands (only show Command::class commands)
            if ($command instanceof Command) {
                return true;
            }

            return false;
        });

        if (empty($commands)) {
            $this->abortCommand('The cli is not supported in this directory');
        }

        $commandListItems = array_values(array_map(function ($command) {
            return '<info>' . $command->getName() . '</info> - ' . $command->getDescription();
        }, $commands));

        $this->printList($commandListItems);

        $commandNames = array_values(array_map(function ($command) {
            return $command->getName();
        }, $commands));

        $choice = $this->printChoice('Select a command', $commandNames);

        // create new application and run command
        // we need this, since symfony/application starts with
        // a default command which is always set
        // https://github.com/symfony/symfony/issues/25632

        $cli = $application->cloneForCommandGroup();
        $cli->addCommands($application->all());
        $command = $cli->find($choice);
        return $command->run($this->input, $this->output);
    }

    protected function printCommandFinish()
    {
        // show nothing here
    }
}
