<?php

namespace Ragnarok\Mobitech\Facades;

use Illuminate\Support\Facades\Facade;
use Ragnarok\Mobitech\Services\MobitechImporter as MImporter;

class MobitechImporter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MImporter::class;
    }
}
