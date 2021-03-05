<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Afeefa\Component\Cli\Cli;
use Afeefa\Component\Cli\Command;
use Afeefa\Component\Cli\Definitions\GroupDefinition as Group;

class Play extends Command
{
    protected function executeCommand()
    {
        $action = $this->getLocalName();
        $pet = $this->getLocalParentName();
        if ($pet === 'cat') {
            $this->printBullet("<info>Cat</info> does not like <info>$action</info>");
        } else {
            $this->printBullet("<info>Dog</info> likes <info>$action</info>");
        }
    }
}

(new Cli('Pets App'))
    ->group('cat', 'Play with cat', function (Group $group) {
        $group->command('hide-seek', Play::class, 'Hide and seek');
    })
    ->group('dog', 'Play with dog', function (Group $group) {
        $group
            ->command('fetch', Play::class, 'Fetch the stick')
            ->command('cuddle', Play::class, 'Cuddle the dog');
    })
    ->run();
