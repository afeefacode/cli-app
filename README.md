# afeefa/cli-app

Create a `symfony/console` PHP cli app with a minimum of configuration.

## Description

At times a project might need a cli tool to perform some configuration, installation or maintenance work. It shouldn't be much effort to get one running. This package is a convenience wrapper around the PHP's [symfony/console](https://github.com/symfony/console) framework and aims to simplify the creation of cli apps. It provides:

* a fluent interface to create (nested) commands
* selectable (sub) commands and command arguments
* reusable actions
* helper functions for input, output and process execution

## Installation

Install via composer as usual. Most probably you use the cli for dev purposes:

```bash
composer require afeefa/cli-app --save-dev
```

## Documentation

See the examples below for inspiration and head over to the documentation pages:

* [Read the Docs](https://afeefa-cli-app.readthedocs.io) on installation, configuration and usage
* the [API Documentation](https://afeefacode.github.io/cli-app/api)

## Examples

1. [Basic Workflow](#example-1-basic-workflow)
2. [Command Actions](#example-2-command-actions)
3. [Command Arguments](#example-3-command-arguments)
3. [Nested Commands](#example-4-nested-commands)

### Example 1: Basic workflow
<a name="example1"></a>

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

### Example 2: Command Actions

The examples shows three things:

* an action, which is kind of a lightweight command, that can be called from any command or action
* a prompt, which lets the user select from a list of choices
* and a possibility to reuse a command by giving it a mode

```php
<?php
...
use Afeefa\Component\Cli\Action;

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

```

Run the example:

```bash
git clone git@github.com:afeefacode/cli-app.git
cd cli-app
composer install

examples/feed/feed
# examples/feed/feed feed-dog
# examples/feed/feed cuddle-cat
```

![output](https://raw.githubusercontent.com/afeefacode/cli-app/main/docs/source/_static/feed.gif "output")

### Example 3: Command Arguments

Command arguments are a basic cli feature. If you want to help the user by providing a list of argument values to choose from, you can use selectable arguments. The example shows:

* using and consuming selectable arguments
* setting a default command

```php
<?php
...
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
```

Run the example:

```bash
git clone git@github.com:afeefacode/cli-app.git
cd cli-app
composer install

examples/walk/walk
# examples/walk/walk
# examples/walk/walk walk dog
# examples/walk/walk walk dog Laika
```

![output](https://raw.githubusercontent.com/afeefacode/cli-app/main/docs/source/_static/walk.gif "output")

### Example 4: Nested Commands

The example shows:

* the configuration of nested commands
* the ability to inspect a commands or a command parent's name.

```php
<?php
...
use Afeefa\Component\Cli\Group;

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

(new Application('Pets App'))
    ->group('cat', 'Play with cat', function (Group $group) {
        $group->command('hide-seek', Play::class, 'Hide and seek');
    })
    ->group('dog', 'Play with dog', function (Group $group) {
        $group
            ->command('fetch', Play::class, 'Fetch the stick')
            ->command('cuddle', Play::class, 'Cuddle the dog');
    })
    ->run();
```

Run the example:

```bash
git clone git@github.com:afeefacode/cli-app.git
cd cli-app
composer install

examples/play/play
# examples/play/play
# examples/play/play dog
# examples/play/play dog:fetch
```

![output](https://raw.githubusercontent.com/afeefacode/cli-app/main/docs/source/_static/play.gif "output")
