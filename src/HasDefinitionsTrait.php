<?php

namespace Afeefa\Component\Cli;

trait HasDefinitionsTrait
{
    protected $commandDefinitions = [];
    protected $noCommandsMessage;
    protected $defaultCommandName;

    public function command(string $name, $Command, string $description): HasDefinitionsInterface
    {
        $definition = new Definition();
        $definition->name = $name;

        if (is_array($Command)) {
            $definition->Command = $Command[0];
            $definition->commandMode = $Command[1] ?? null;
        } else {
            $definition->Command = $Command;
        }

        $definition->description = $description;

        $this->commandDefinitions[] = $definition;

        return $this;
    }

    public function group(string $name, string $description, callable $callback): HasDefinitionsInterface
    {
        $group = new Group();
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

    function default(string $name): HasDefinitionsInterface {
        $this->defaultCommandName = $name;
        return $this;
    }

    public function definitionsToCommands(Application $app, ?Command $parentCommand = null): array
    {
        $commands = [];

        /** @var HasDefinitionsInterface */
        foreach ($this->commandDefinitions as $definition) {
            $Command = $definition->Command;

            /** @var Command */
            if ($Command === CommandGroup::class) {
                $command = new CommandGroup($app, null, $definition->getDefaultCommandName(), $definition->getNoCommandsMessage());
            } else {
                $command = new $Command($app);
            }

            $commandName = $definition->name;
            if ($parentCommand) {
                $commandName = $parentCommand->getName() . ':' . $commandName;
            }

            $command->setName($commandName);
            $command->setDescription($definition->description ?: 'Select a command');
            $command->setCommandMode($definition->commandMode);
            $command->setParentCommand($parentCommand);

            $commands[] = $command;

            $subCommands = $definition->definitionsToCommands($app, $command);

            $commands = [...$commands, ...$subCommands];
        }

        return $commands;
    }

    public function getNoCommandsMessage(): ?string
    {
        return $this->noCommandsMessage;
    }

    public function getDefaultCommandName(): ?string
    {
        return $this->defaultCommandName;
    }
}
