<?php

namespace Afeefa\Component\Cli;

class Definition implements HasDefinitionsInterface
{
    use HasDefinitionsTrait;

    public $name;
    public $Command;
    public $description;
    public $mode;
}
