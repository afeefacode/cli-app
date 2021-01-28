<?php

namespace Afeefa\Component\Cli;

use Symfony\Component\Console\Style\SymfonyStyle;

class Action
{
    use CommandActionTrait {
        CommandActionTrait::printActionTitle as outputPrintActionTitle;
        CommandActionTrait::printSubActionTitle as outputPrintSubActionTitle;
    }

    public const TITLE_NORMAL = 'normal-title';
    public const TITLE_HIDDEN = 'hide-title';
    public const TITLE_SMALL = 'small-title';

    /**
     * @var string
     */
    protected $cwd = null;

    /**
     * @var string
     */
    protected $titleFormat = null;

    /**
     * @var array
     */
    protected $args = [];

    public function __construct(SymfonyStyle $io, string $titleFormat)
    {
        $this->io = $io;
        $this->titleFormat = $titleFormat;
    }

    public function run(array $args = [])
    {
        $this->args = $args;

        if ($this->titleFormat === self::TITLE_NORMAL) {
            $this->printActionTitle($this->getActionTitle());
        } else if ($this->titleFormat === self::TITLE_SMALL) {
            $this->printSubActionTitle($this->getActionTitle());
        }

        return $this->executeAction();
    }

    protected function getActionTitle()
    {
        return get_class($this);
    }

    protected function executeAction()
    {
        return 0;
    }

    protected function getArgument(string $key, $default = null)
    {
        return $this->args[$key] ?? $default;
    }

    protected function printActionTitle(string $title)
    {
        $this->outputPrintActionTitle($title);
    }

    protected function printSubActionTitle(string $title)
    {
        $this->outputPrintSubActionTitle($title);
    }
}
