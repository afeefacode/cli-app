<?php

use Afeefa\Component\Package\Package\Package;
use Afeefa\Component\Package\ReleaseManager;

return (new ReleaseManager())
    ->packages([
        Package::composer()
            ->path(getcwd())
    ]);
