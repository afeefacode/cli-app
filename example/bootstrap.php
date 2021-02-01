<?php

use Afeefa\Component\Cli\Application;
use Afeefa\Component\Cli\HasDefinitionsInterface;
use Example\CommandWithAction;
use Example\CommandWithMode;
use Example\LslCommand;

require_once __DIR__ . '/../vendor/autoload.php';

(new Application('Example Cli App'))

    ->command('ls', LslCommand::class, 'Run ls -la in current folder')
    ->command('action', CommandWithAction::class, 'Run a command with an action')

    ->group('modes', 'Select a mode', function (HasDefinitionsInterface $group) {
        $group
            ->command('mode', CommandWithMode::class, 'This is the default mode')
            ->command('mode1', [CommandWithMode::class, 'one'], 'This is the mode one')
            ->command('mode2', [CommandWithMode::class, 'two'], 'This is the mode two');
        // ->noCommandAvailable('There is no command in this group');
    })

    // ->noCommandAvailable('There is no command defined')
    // ->dumpCommandDefinitions()
    // ->dumpCommands()

    ->infos([
        'Hello' => 'Wie geht\'s?',
        'Yes' => 'I am pretty well'
    ])

    ->run();
