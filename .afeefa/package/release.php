<?php

use Afeefa\Component\Package\Package\Package;
use Afeefa\Component\Package\ReleaseManager;
use Webmozart\PathUtil\Path;

return (new ReleaseManager())
    ->packages([
        Package::composer()
            ->path(Path::join(__DIR__, '..', '..'))
    ]);
