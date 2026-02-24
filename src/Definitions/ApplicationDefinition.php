<?php

namespace Afeefa\Component\Cli\Definitions;

use Afeefa\Component\Cli\Application;
use Afeefa\Component\Cli\Command;
use Afeefa\Component\Cli\CommandGroup;

class ApplicationDefinition extends GroupDefinition
{
    /**
     * Infos shown in CLI header
     */
    protected $infos = [];

    /**
     * Action to run in CLI header
     *
     * @var string|null
     */
    protected $HeaderAction;

    /**
     * Command to run before any command of this app
     *
     * @var string
     */
    protected $BeforeCommand;

    public function __construct(?string $name = '')
    {
        $this->name = $name;
    }

    public function command(string $name, $Command, string $description): ApplicationDefinition
    {
        return parent::command($name, $Command, $description);
    }

    public function group(string $name, string $description, callable $callback): ApplicationDefinition
    {
        return parent::group($name, $description, $callback);
    }

    public function beforeAction(string $BeforeAction, array $params = []): ApplicationDefinition
    {
        return parent::beforeAction($BeforeAction, $params = []);
    }

    public function noCommandAvailable(string $message): ApplicationDefinition
    {
        return parent::noCommandAvailable($message);
    }

    public function default(string $name): ApplicationDefinition {
        return parent::default($name);
    }

    /**
     * Define a command to run before any command of this app
     */
    public function beforeCommand(string $BeforeCommand): ApplicationDefinition
    {
        $this->BeforeCommand = $BeforeCommand;
        return $this;
    }

    /**
     * Return the before command
     */
    public function getBeforeCommand(): ?string
    {
        return $this->BeforeCommand;
    }

    /**
     * Set an action to run in CLI header
     */
    public function headerAction(string $HeaderAction): ApplicationDefinition
    {
        $this->HeaderAction = $HeaderAction;
        return $this;
    }

    /**
     * Return the header action
     */
    public function getHeaderAction(): ?string
    {
        return $this->HeaderAction;
    }

    /**
     * Set infos to be shown in CLI header
     */
    public function infos(array $infos): ApplicationDefinition
    {
        $this->infos = $infos;
        return $this;
    }

    /**
     * Get the cli header infos
     */
    public function getInfos(): array
    {
        return $this->infos;
    }

    public function run(): void
    {
        $Application = $this->getApplicationClass();
        $app = new $Application($this->name);

        $commands = $this->definitionsToCommands($app);

        $indexCommand = new CommandGroup($app, 'index', $this); // i am the definition by myself
        $indexCommand->setDescription('Select a command');
        $app->add($indexCommand);

        if ($this->defaultCommandName) {
            $app->setDefaultCommand($this->defaultCommandName);
        } else {
            $app->setDefaultCommand($indexCommand->getName());
        }

        foreach ($commands as $command) {
            $app->add($command);
        }

        $app->infos($this->infos);
        $app->setApplicationDefinition($this);

        $app->run();
    }

    public function dumpCommandDefinitions(): ApplicationDefinition
    {
        $definitions = array_map(function (CommandDefinition $definition) {
            return $definition->toArray();
        }, $this->definitions);
        debug_dump($definitions);
        return $this;
    }

    public function dumpCommands(): ApplicationDefinition
    {
        $app = new Application($this->name);
        $commands = $this->definitionsToCommands($app);
        $commands = array_map(function (Command $commmand) {
            return $commmand->toArray();
        }, $commands);
        debug_dump($commands);
        return $this;
    }

    protected function getApplicationClass(): string
    {
        return Application::class;
    }
}
