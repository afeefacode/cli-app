<?php

namespace Afeefa\Component\Cli;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
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
     * @var string
     */
    protected $mode;

    /**
     * @var Benchmark
     */
    protected static $cliBenchmark;

    public function __construct(Application $application, string $name = null)
    {
        $this->setApplication($application);

        parent::__construct($name);
    }

    public function configure()
    {
        $this->setArguments();
    }

    protected function setArguments()
    {
    }

    public function setMode(?string $mode)
    {
        $this->mode = $mode;
    }

    public function getMode(?string $default = null): ?string
    {
        return $this->mode ?: $default;
    }

    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'class' => get_class($this),
            'description' => $this->getDescription(),
            'mode' => $this->mode
        ];
    }

    protected function getArgument(string $key, $default = null)
    {
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

    protected function printCommandFinish()
    {
        if ($this->taskInfo) {
            $diffCli = self::$cliBenchmark->getDiff();
            $commandFinishTime = preg_replace("/ \+.+/", '', $diffCli);

            $diffCommand = $this->commandBenchmark->getDiff();
            $commandDuration = preg_replace("/.+\+/", '', $diffCommand);

            $this->io->newLine();
            $this->printText("<info>OK: $this->taskInfo</info>");

            $text = "time finish: $commandFinishTime, command duration: $commandDuration sec";
            $this->printText($text);

            $this->printText(str_repeat('-', strlen($text)));

            $this->io->newLine();
        }
    }
}
