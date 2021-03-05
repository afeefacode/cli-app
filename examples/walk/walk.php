<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Afeefa\Component\Cli\Application;
use Afeefa\Component\Cli\Command;
use Afeefa\Component\Cli\SelectableArgument;

class Walk extends Command
{
    protected function setArguments()
    {
        $this->addSelectableArgument( // selectable argument
            'pet', ['cat', 'dog'], 'The pet to walk with'
        );

        $this->addSelectableArgument( // dependent argument
            'name',
            function (SelectableArgument $argument) {
                $pet = $this->getArgument('pet');
                $argument->choices = $pet === 'cat'
                    ? ['Kitty', 'Tiger', 'Meow']
                    : ['Laika', 'Lassie', 'Goofy'];
                $argument->description = 'The name of the ' . $pet;
            }
        );
    }

    protected function executeCommand()
    {
        $pet = $this->getArgument('pet');
        $name = $this->getArgument('name');

        if ($pet === 'cat') {
            $this->printBullet("<info>$name</info> does not walk with you");
        } else {
            $this->printBullet("<info>$name</info> is happy to walk with you");
        }
    }
}

(new Application('Pets App'))
    ->command('walk', Walk::class, 'Walk with a pet')
    ->default('walk')
    ->run();
