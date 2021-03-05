<?php

namespace Afeefa\Component\Cli\Definitions;

class CommandDefinition
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $Command;

    /**
     * @var string
     */
    public $commandMode;

    /**
     * @var string
     */
    public $description;

    /**
     * @var GroupDefinition
     */
    public $group;

    public function toArray()
    {
        $array = [
            'name' => $this->name,
            'Command' => $this->Command,
            'mode' => $this->commandMode
        ];

        if ($this->group) {
            $array['group'] = $this->group->toArray();
        }

        return $array;
    }
}
