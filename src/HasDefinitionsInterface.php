<?php

namespace Afeefa\Component\Cli;

interface HasDefinitionsInterface
{
    public function command(string $name, $Command, string $description): HasDefinitionsInterface;

    public function group(string $name, string $description, callable $callback): HasDefinitionsInterface;

    public function noCommandAvailable(string $message): HasDefinitionsInterface;

    public function definitionsToCommands(Application $app, ?string $parentName = null): array;

    public function getNoCommandsMessage(): ?string;
}
