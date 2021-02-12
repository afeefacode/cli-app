<?php

namespace Afeefa\Component\Cli;

interface HasDefinitionsInterface
{
    public function command(string $name, $Command, string $description): HasDefinitionsInterface;

    public function group(string $name, string $description, callable $callback): HasDefinitionsInterface;

    public function default(string $name): HasDefinitionsInterface;

    public function noCommandAvailable(string $message): HasDefinitionsInterface;

    public function definitionsToCommands(Application $app, ?Command $parentCommand = null): array;

    public function getDefaultCommandName(): ?string;

    public function getNoCommandsMessage(): ?string;
}
