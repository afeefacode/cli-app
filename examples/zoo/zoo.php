<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Afeefa\Component\Cli\Cli;
use Afeefa\Component\Cli\Command;

class AddAnimal extends Command
{
    protected function executeCommand()
    {
        $result = $this->printMultichoice('Select multiple animals to jail:', ['Bear', 'Eagle', 'Alligator'], '0, 1');
        $animals = implode(', ', $result);
        $this->printBullet("<info>{$animals}</info> live now in the zoo.");
    }
}

(new Cli('Zoo App'))
    ->command('add', AddAnimal::class, 'Add animal') // 'cat' = mode
    ->run();
