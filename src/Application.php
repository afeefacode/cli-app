<?php

namespace Afeefa\Component\Cli;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Wujunze\Colors;

class Application extends SymfonyApplication implements HasDefinitionsInterface
{
    use HasDefinitionsTrait {
        HasDefinitionsTrait::command as definitionsCommand;
        HasDefinitionsTrait::group as definitionsGroup;
        HasDefinitionsTrait::noCommandAvailable as definitionsNoCommandsAvailable;
    }

    protected $BeforeCommand;
    protected $infos = [];

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $commands = $this->definitionsToCommands($this);

        $indexCommand = new CommandGroup($this, 'index', $this->noCommandsMessage);
        $indexCommand->setDescription('Select a command');
        $this->add($indexCommand);
        $this->setDefaultCommand($indexCommand->getName());

        foreach ($commands as $command) {
            $this->add($command);
        }

        if ($this->getName()) {
            $this->printCliHeader();
        }

        if ($this->BeforeCommand) {
            $command = new $this->BeforeCommand($this);
            $input = new ArgvInput();
            $output = new ConsoleOutput();
            $command->run($input, $output);
        }

        return parent::run($input, $output);
    }

    public function runBefore(string $Command): Application
    {
        $this->BeforeCommand = $Command;
        return $this;
    }

    /**
     * Just cast to Application to be usable in fluent interface
     */
    public function command(string $name, $Command, string $description): Application
    {
        return $this->definitionsCommand($name, $Command, $description);
    }

    /**
     * Just cast to Application to be usable in fluent interface
     */
    public function group(string $name, string $description, callable $callback): Application
    {
        return $this->definitionsGroup($name, $description, $callback);
    }

    /**
     * Just cast to Application to be usable in fluent interface
     */
    public function noCommandAvailable(string $message): Application
    {
        return $this->definitionsNoCommandsAvailable($message);
    }

    public function infos(array $infos)
    {
        $this->infos = $infos;
        return $this;
    }

    /**
     * Removes default app name output
     */
    public function getLongVersion()
    {
        return '';
    }

    public function dumpCommandDefinitions(): Application
    {
        debug_dump($this->commandDefinitions);
        return $this;
    }

    public function dumpCommands(): Application
    {
        $commands = $this->definitionsToCommands($this);
        $commands = array_map(function (Command $commmand) {
            return $commmand->toArray();
        }, $commands);
        debug_dump($commands);
        return $this;
    }

    private function printCliHeader(): void
    {
        $colors = new Colors();

        $lineLength = 50;

        echo "\n";
        echo $colors->getColoredString('/' . str_repeat('*', $lineLength), 'light_gray');
        echo "\n";
        echo $colors->getColoredString(' * ', 'light_gray');
        echo $colors->getColoredString($this->getName(), 'brown');
        echo "\n";

        if (count($this->infos)) {
            echo $colors->getColoredString(' * ', 'light_gray');
            echo "\n";
        }

        foreach ($this->infos as $key => $value) {
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
