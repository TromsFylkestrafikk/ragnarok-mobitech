<?php

namespace Ragnarok\Mobitech\Facades;

use Illuminate\Support\Facades\Facade;
use Ragnarok\Mobitech\Services\MobitechImporter as MImporter;

/**
 * @method static MImporter import(string $id, string $file)
 * @method static MImporter deleteImport(string $id)
 */
class MobitechImporter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MImporter::class;
    }
}
