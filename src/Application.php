<?php

namespace Afeefa\Component\Cli;

use Symfony\Component\Console\Application as SymfonyApplication;

class Application extends SymfonyApplication
{
    public function __construct(string $name = '', array $infos = [], CommandDefinition $commandDefinitions = null)
    {
        parent::__construct($name);

        if ($commandDefinitions) {
            $commands = $this->definitionsToCommands(null, $commandDefinitions);

            // $this->dumpCommands($commands);

            foreach ($commands as $command) {
                $this->add($command);
            }

            $defaultCommand = $commands[0];
            $this->add($defaultCommand);
            $this->setDefaultCommand($defaultCommand->getName());
        }

        if ($name) {
            $this->printCliHeader($infos);
        }
    }

    public function cloneForCommandGroup(): Application
    {
        return new Application();
    }

    /**
     * Removes default app name output
     */
    public function getLongVersion()
    {
        return '';
    }

    private function dumpCommands(array $commands): void
    {
        $commands = array_map(function (Command $commmand) {
            return $commmand->toArray();
        }, $commands);
        print_r($commands);
    }

    private function definitionsToCommands(?string $parentName, CommandDefinition $commandDefinition)
    {
        $flat = [];

        $Command = $commandDefinition->Command ?: CommandGroup::class;
        /** @var Command */
        $command = new $Command($this);

        $commandName = $commandDefinition->name;
        if ($parentName && $parentName !== 'index') {
            $commandName = $parentName . ':' . $commandName;
        }

        $command->setName($commandName);
        $command->setDescription($commandDefinition->description ?: 'Select a command');
        $command->setMode($commandDefinition->mode);

        $flat[] = $command;

        /** @var CommandDefinition */
        foreach ($commandDefinition->subCommands as $subCommand) {
            $subCommands = $this->definitionsToCommands($commandName, $subCommand);
            foreach ($subCommands as $subCommand) {
                $flat[] = $subCommand;
            }
        }

        return $flat;
    }

    private function printCliHeader($messages = []): void
    {
        $colors = new \Wujunze\Colors();

        $lineLength = 50;

        echo "\n";
        echo $colors->getColoredString('/' . str_repeat('*', $lineLength), 'light_gray');
        echo "\n";
        echo $colors->getColoredString(' * ', 'light_gray');
        echo $colors->getColoredString($this->getName(), 'brown');
        echo "\n";

        if (count($messages)) {
            echo $colors->getColoredString(' * ', 'light_gray');
            echo "\n";
        }

        foreach ($messages as $key => $value) {
            echo $colors->getColoredString(' * ', 'light_gray');
            echo $key . ': ';

            if (is_array($value)) {
                echo "\n";
                foreach ($value as $info) {
                    echo $colors->getColoredString(' * ', 'light_gray');
                    echo '  - ';
                    echo $colors->getColoredString($info, 'green') . "\n";
                }
            } else {
                echo $colors->getColoredString($value, 'green') . "\n";

            }
        }

        echo $colors->getColoredString(' ' . str_repeat('*', $lineLength) . '/', 'light_gray');
        echo "\n";
    }
}
