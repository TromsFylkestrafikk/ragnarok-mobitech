<?php

namespace Ragnarok\Mobitech\Facades;

use Illuminate\Support\Facades\Facade;
use Ragnarok\Mobitech\Services\MobitechFiles as MFiles;

class MobitechFiles extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MFiles::class;
    }
}
