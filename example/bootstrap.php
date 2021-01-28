<?php

use Afeefa\Component\Cli\Application;
use Afeefa\Component\Cli\CommandDefinition;
use Example\CommandWithAction;
use Example\SimpleCommand;

require_once __DIR__ . '/../vendor/autoload.php';

$infos = [
    'Hello' => 'Wie geht\'s?',
    'Yes' => 'I am pretty well'
];

$commands = new CommandDefinition();

$commands->add('ls', SimpleCommand::class, 'Run ls -la in current folder');
$commands->add('action', CommandWithAction::class, 'Run a command with an action');

$cli = new Application('Cli App', $infos, $commands);

$cli->run();
