# afeefa/cli-app

Create a `symfony/console` PHP cli app with a minimum of configuration.

## Description

At times a project might need a cli tool to perform some configuration, installation or maintenance work. It shouldn't be much effort to get one running. This package is a convenience wrapper around the PHP's [symfony/console](https://github.com/symfony/console) framework and aims to simplify the creation of cli apps. It provides:

* a fluent interface to create (nested) commands
* selectable (sub) commands
* reusable actions
* helper functions for input, output and process execution

## Basic Example

The most basic example shows the workflow of `cli-app`. You create one or more commands (usually in a separate file) and add those commands to the application instance by providing a command name and a description.

File: `pets.php`

```php
<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Afeefa\Component\Cli\Application;
use Afeefa\Component\Cli\Command;

class Cats extends Command
{
    protected function executeCommand()
    {
        $this->printList(['Molly', 'Felix', 'Minka']);
    }
}

class Dogs extends Command
{
    protected function executeCommand()
    {
        $this->printList(['Bella', 'Charlie', 'Luna']);
    }
}

(new Application('Pets App'))
    ->command('cats', Cats::class, 'Show cats')
    ->command('dogs', Dogs::class, 'Show dogs')
    ->run();
```

Run the example:

```bash
git clone git@github.com:afeefacode/cli-app.git
cd cli-app
composer install

examples/pets/pets
# examples/pets/pets cats
```

![output](https://raw.githubusercontent.com/afeefacode/cli-app/main/docs/source/_static/pets.gif "output")

## Example with actions and arguments

The examples shows a reusable action and two possibilities to configure a command.

```php
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
        return $this->printChoice("Select a $pet", $names);
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
    protected function setArguments()
    {
        $this->addSelectableArgument( // selectable argument
            'pet', ['cat', 'dog'], 'The pet to cuddle'
        );
    }

    protected function executeCommand()
    {
        $pet = $this->getArgument('pet');
        $name = $this->runAction(Names::class, ['pet' => $pet]);
        $this->printBullet("Cuddle <info>$name</info>");
    }
}

(new Application('Pets App'))
    ->command('feed-cat', [Feed::class, 'cat'], 'Feed a cat') // command mode
    ->command('feed-dog', [Feed::class, 'dog'], 'Feed a dog')
    ->command('cuddle', Cuddle::class, 'Cuddle a pet')
    ->run();
```

Run the example:

```bash
git clone git@github.com:afeefacode/cli-app.git
cd cli-app
composer install

examples/feed/feed
# examples/feed/feed feed-dog
# examples/feed/feed cuddle dog
```

![output](https://raw.githubusercontent.com/afeefacode/cli-app/main/docs/source/_static/feed.gif "output")

## Installation

Install via composer as usual:

```bash
composer require afeefa/cli-app
```

## Documentation

https://afeefa-cli-app.readthedocs.io
