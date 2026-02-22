<?php

namespace Afeefa\Component\Cli;

use Afeefa\Component\Cli\Definitions\ApplicationDefinition;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Wujunze\Colors;

class Application extends SymfonyApplication
{
    /**
     * @var ApplicationDefinition
     */
    protected $applicationDefinition;

    protected array $infos;

    protected string $BeforeCommand;

    public function run(?InputInterface $input = null, ?OutputInterface $output = null): int
    {
        if ($this->getName()) {
            $this->printCliHeader();
        }

        if ($BeforeCommand = $this->applicationDefinition->getBeforeCommand()) {
            $command = new $BeforeCommand($this);
            $input = new ArgvInput();
            $output = new ConsoleOutput();
            $command->run($input, $output);
        }

        return parent::run($input, $output);
    }

    /**
     * Sets the app definition
     */
    public function setApplicationDefinition(ApplicationDefinition $applicationDefinition)
    {
        $this->applicationDefinition = $applicationDefinition;

        return $this;
    }

    public function beforeCommand(string $Command)
    {
        $this->BeforeCommand = $Command;
        return $this;
    }

    public function infos(array $infos)
    {
        $this->infos = $infos;
        return $this;
    }

    /**
     * Removes default app name output
     */
    public function getLongVersion(): string
    {
        return '';
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

        $infos = $this->applicationDefinition->getInfos();

        if (count($infos)) {
            echo $colors->getColoredString(' * ', 'light_gray');
            echo "\n";
        }

        foreach ($infos as $key => $value) {
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
