<?php

namespace Afeefa\Component\Cli\Definitions;

use Afeefa\Component\Cli\Command;
use Afeefa\Component\Cli\CommandGroup;
use Symfony\Component\Console\Application;

class GroupDefinition extends CommandDefinition
{
    /**
     * List of sub commands / groups
     *
     * @var array
     */
    protected $definitions = [];

    /**
     * Action to run before any command of this group
     *
     * @var string
     */
    protected $BeforeAction;

    /**
     * Params for the before action
     *
     * @var array
     */
    protected $beforeActionParams = [];

    /**
     * Message shown if no commands are available
     *
     * @var string
     */
    protected $noCommandsMessage;

    /**
     * Name of a command to be run as default for this group
     *
     * @var string
     */
    protected $defaultCommandName;

    /**
     * Add a command
     */
    public function command(string $name, $Command, string $description): GroupDefinition
    {
        $command = new CommandDefinition();
        $command->name = $name;

        if (is_array($Command)) {
            $command->Command = $Command[0];
            $command->commandMode = $Command[1] ?? null;
        } else {
            $command->Command = $Command;
        }

        $command->description = $description;
        $command->group = $this;

        $this->definitions[] = $command;

        return $this;
    }

    /**
     * Add a group
     */
    public function group(string $name, string $description, callable $callback): GroupDefinition
    {
        $group = new GroupDefinition();

        $group->name = $name;
        $group->Command = CommandGroup::class;

        $group->description = $description;
        $group->group = $this;

        $callback($group);

        $this->definitions[] = $group;

        return $this;
    }

    /**
     * Define an action to run before any command of this group
     */
    public function beforeAction(string $BeforeAction, array $params = []): GroupDefinition
    {
        $this->BeforeAction = $BeforeAction;
        $this->beforeActionParams = $params;
        return $this;
    }

    /**
     * Return the before action
     */
    public function getBeforeAction(): ?string
    {
        return $this->BeforeAction;
    }

    /**
     * Return the before action params
     */
    public function getBeforeActionParams(): array
    {
        return $this->beforeActionParams;
    }

    /**
     * Set a message shown if no command is available in this group
     */
    public function noCommandAvailable(string $message): GroupDefinition
    {
        $this->noCommandsMessage = $message;
        return $this;
    }

    /**
     * Returns the no commands available message
     */
    public function getNoCommandsMessage(): ?string
    {
        return $this->noCommandsMessage;
    }

    /**
     * Set a command of this group to be run instead of the index
     */
    public function default(string $name): GroupDefinition {
        $this->defaultCommandName = $name;
        return $this;
    }

    /**
     * Returns the default command name
     */
    public function getDefaultCommandName(): ?string
    {
        return $this->defaultCommandName;
    }

    /**
     * Converts command definitions to actual commands
     */
    public function definitionsToCommands(Application $app, ?Command $parentCommand = null): array
    {
        $commands = [];

        foreach ($this->definitions as $definition) {

            $isGroup = $definition instanceof GroupDefinition;
            $command = null;

            if ($isGroup) {
                $command = new CommandGroup($app, $definition);
            } else { // Command
                $Command = $definition->Command;
                $command = new $Command($app);
            }

            /** @var Command $command */

            $commandName = $definition->name;
            if ($parentCommand) {
                $commandName = $parentCommand->getName() . ':' . $commandName;
            }
            $command->setName($commandName);
            $command->setDescription($definition->description ?: 'Select a command');

            $command->setCommandMode($definition->commandMode);
            $command->setCommandDefinition($definition);

            $command->setParentCommand($parentCommand);

            $commands[] = $command;

            if ($isGroup) {
                $subCommands = $definition->definitionsToCommands($app, $command);
                $commands = [...$commands, ...$subCommands];
            }
        }

        return $commands;
    }

    public function toArray()
    {
        $array = parent::toArray();

        $array['BeforeAction'] = $this->BeforeAction;

        return $array;
    }
}
