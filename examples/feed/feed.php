<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Afeefa\Component\Cli\Action;
use Afeefa\Component\Cli\Application;
use Afeefa\Component\Cli\Command;

class Names extends Action
{
    protected function executeAction()
    {
        $pet = $this->getArgument('pet');
        $names = $pet === 'cat'
            ? ['Kitty', 'Tiger', 'Meow']
            : ['Laika', 'Lassie', 'Goofy'];
        return $this->printChoice("Select a $pet", $names); // prompt
    }
}

class Feed extends Command
{
    protected function executeCommand()
    {
        $pet = $this->getCommandMode();
        $name = $this->runAction(Names::class, ['pet' => $pet]);
        $this->printBullet("Feed <info>$name</info>");
    }
}

class Cuddle extends Command
{
    protected function executeCommand()
    {
        $name = $this->runAction(Names::class, ['pet' => 'cat']);
        $this->printBullet("Cuddle <info>$name</info>");
    }
}

(new Application('Pets App'))
    ->command('feed-cat', [Feed::class, 'cat'], 'Feed a cat') // 'cat' = mode
    ->command('feed-dog', [Feed::class, 'dog'], 'Feed a dog')
    ->command('cuddle-cat', Cuddle::class, 'Cuddle a cat')
    ->run();
