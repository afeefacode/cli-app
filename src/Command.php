<?php

namespace Afeefa\Component\Cli;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Command extends SymfonyCommand
{
    use CommandActionTrait;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var string
     */
    protected $taskInfo;

    /**
     * @var Benchmark
     */
    protected $commandBenchmark;

    /**
     * @var Benchmark
     */
    protected static $cliBenchmark;

    /**
     * @var string
     */
    protected $commandMode;

    /**
     * @var array
     */
    protected $selectableArgumentChoices = [];

    /**
     * @var array
     */
    protected $selectableArgumentValues = [];

    /**
     * @var Command
     */
    protected $parentCommand;

    public function __construct(Application $application, string $name = null)
    {
        $this->setApplication($application);

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setArguments();
    }

    protected function setArguments()
    {
    }

    protected function addSelectableArgument(string $name, $choices, string $description = '', $default = null)
    {
        $this->selectableArgumentChoices[$name] = $choices;
        $this->addArgument($name, InputArgument::OPTIONAL, $description, $default);
        return $this;
    }

    public function setCommandMode(?string $commandMode)
    {
        $this->commandMode = $commandMode;
    }

    public function getCommandMode(?string $default = null): ?string
    {
        return $this->commandMode ?: $default;
    }

    public function getParentCommand(): ?Command
    {
        return $this->parentCommand;
    }

    public function setParentCommand(Command $parentCommand = null)
    {
        $this->parentCommand = $parentCommand;

        return $this;
    }

    public function getLocalName(): string
    {
        return preg_replace('/^.+\:/', '', $this->getName());
    }

    public function getLocalParentName(): ?string
    {
        $parent = $this->getParentCommand();
        if ($parent) {
            return preg_replace('/^.+\:/', '', $parent->getName());
        }
        return null;
    }

    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'class' => get_class($this),
            'description' => $this->getDescription(),
            'mode' => $this->commandMode
        ];
    }

    protected function getArgument(string $key, $default = null)
    {
        if (array_key_exists($key, $this->selectableArgumentValues)) {
            return $this->selectableArgumentValues[$key];
        }

        return $this->input->getArgument($key) ?: $default;
    }

    protected function getOption(string $key, $default = null)
    {
        return $this->input->getOption($key) ?: $default;
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        $result = parent::run($input, $output);

        $this->printCommandFinish();

        return $result;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input = $input;

        $this->io = new SymfonyStyle($input, $output);

        $this->printCommandStart($this->getCommandTitle());

        // select required arguments
        $definition = $this->getNativeDefinition();
        foreach ($definition->getArguments() as $argumentName => $argument) {
            if (isset($this->selectableArgumentChoices[$argumentName])) {
                $choices = $this->selectableArgumentChoices[$argumentName];
                if (is_callable($choices)) {
                    $choices = $choices();
                }
                if (!count($choices)) {
                    $this->abortCommand($argument->getDescription());
                }
                $value = $this->getArgument($argumentName);
                if (!$value || !in_array($value, $choices)) {
                    $value = $this->printChoice('Select ' . lcfirst($argument->getDescription()), $choices);
                    $this->selectableArgumentValues[$argumentName] = $value;

                }
            }
        }

        return $this->executeCommand() ?: 0;
    }

    protected function getCommandTitle()
    {
        return $this->getDescription() ?: $this->getName();
    }

    protected function executeCommand()
    {
        return 0;
    }

    protected function printCommandStart($info)
    {
        if ($info) {
            $this->io->title($info);

            if (!self::$cliBenchmark) {
                self::$cliBenchmark = BenchmarkUtil::startBenchmark();
            }
            $this->commandBenchmark = BenchmarkUtil::startBenchmark();

            $this->taskInfo = $info;
        }
    }

    protected function printCommandFinish(): void
    {
        if ($this->taskInfo) {
            $diffCli = self::$cliBenchmark->getDiff();
            $commandFinishTime = preg_replace("/ \+.+/", '', $diffCli);

            $diffCommand = $this->commandBenchmark->getDiff();
            $commandDuration = preg_replace("/.+\+/", '', $diffCommand);

            $this->io->newLine();
            $this->printText("<info>OK: $this->taskInfo</info>");

            $this->printText($this->getCommandUsed());

            $text = "time finish: $commandFinishTime, command duration: $commandDuration sec";
            $this->printText($text);

            $this->printText(str_repeat('-', strlen($text)));

            $this->io->newLine();
        }
    }

    protected function getCommandUsed(): string
    {
        $args = [$_SERVER['argv'][0], $this->getName()];

        $definition = $this->getNativeDefinition();

        foreach ($definition->getArguments() as $argumentName => $_) {
            if (array_key_exists($argumentName, $this->selectableArgumentValues)) {
                $args[] = $this->selectableArgumentValues[$argumentName];
            } else {
                $value = $this->getArgument($argumentName);
                if ($value) {
                    $args[] = $this->getArgument($argumentName);
                }
            }
        }

        foreach ($definition->getOptions() as $optionName => $_) {
            if ($this->getOption($optionName)) {
                $args[] = '--' . $optionName;
            }
        }

        return implode(' ', $args);

    }
}
