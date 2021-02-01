<?php

namespace Afeefa\Component\Cli;

trait HasDefinitionsTrait
{
    protected $commandDefinitions = [];
    protected $noCommandsMessage = null;

    public function command(string $name, $Command, string $description): HasDefinitionsInterface
    {
        $definition = new Definition();
        $definition->name = $name;

        if (is_array($Command)) {
            $definition->Command = $Command[0];
            $definition->mode = $Command[1] ?? null;
        } else {
            $definition->Command = $Command;
        }

        $definition->description = $description;

        $this->commandDefinitions[] = $definition;

        return $this;
    }

    public function group(string $name, string $description, callable $callback): HasDefinitionsInterface
    {
        $group = new Definition();
        $group->name = $name;
        $group->description = $description;
        $group->Command = CommandGroup::class;

        $callback($group);

        $this->commandDefinitions[] = $group;

        return $this;
    }

    public function noCommandAvailable(string $message): HasDefinitionsInterface
    {
        $this->noCommandsMessage = $message;
        return $this;
    }

    public function definitionsToCommands(Application $app, ?string $parentName = null): array
    {
        $commands = [];

        /** @var HasDefinitionsInterface */
        foreach ($this->commandDefinitions as $Definition) {
            $Command = $Definition->Command;

            /** @var Command */
            if ($Command === CommandGroup::class) {
                $command = new CommandGroup($app, null, $Definition->getNoCommandsMessage());
            } else {
                $command = new $Command($app);
            }

            $commandName = $Definition->name;
            if ($parentName) {
                $commandName = $parentName . ':' . $commandName;
            }

            $command->setName($commandName);
            $command->setDescription($Definition->description ?: 'Select a command');
            $command->setMode($Definition->mode);

            $commands[] = $command;

            $subCommands = $Definition->definitionsToCommands($app, $commandName);

            $commands = [...$commands, ...$subCommands];
        }

        return $commands;
    }

    public function getNoCommandsMessage(): ?string
    {
        return $this->noCommandsMessage;
    }
}
