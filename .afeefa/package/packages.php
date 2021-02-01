<?php

use Afeefa\Component\Package\Package\Composer;
use Webmozart\PathUtil\Path;

return [
    (new Composer())
        ->path(Path::join(__DIR__, '..', '..'))
];
