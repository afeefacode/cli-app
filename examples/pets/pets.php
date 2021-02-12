<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Afeefa\Component\Cli\Application;
use Afeefa\Component\Cli\Command;

class Cats extends Command
{
    protected function executeCommand()
    {
        $this->printList(['Kitty', 'Tiger', 'Meow']);
    }
}

class Dogs extends Command
{
    protected function executeCommand()
    {
        $this->printList(['Laika', 'Lassie', 'Goofy']);
    }
}

(new Application('Pets App'))
    ->command('cats', Cats::class, 'Show cats')
    ->command('dogs', Dogs::class, 'Show dogs')
    ->run();