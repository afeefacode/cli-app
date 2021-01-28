<?php

namespace Afeefa\Component\Cli;

class CommandDefinition
{
    public $name = 'index';
    public $Command = null;
    public $description;
    public $mode;
    public $subCommands = [];
    public $abortMessage = null;

    public function add(string $name, $Command, string $description = null): CommandDefinition
    {
        $this->addDefinition($name, $Command, $description);
        return $this;
    }

    public function abort(string $message): void
    {
        $this->abortMessage = $message;
    }

    public function addAndGet(string $name, $Command, string $description = null): CommandDefinition
    {
        $definition = $this->addDefinition($name, $Command, $description);
        return $definition;
    }

    private function addDefinition(string $name, $Command, string $description): CommandDefinition
    {
        $definition = new CommandDefinition();
        $definition->name = $name;

        if (is_array($Command)) {
            $definition->Command = $Command[0];
            $definition->mode = $Command[1] ?? null;
        } else {
            $definition->Command = $Command;
        }

        $definition->description = $description;

        $this->subCommands[] = $definition;

        return $definition;
    }
}
