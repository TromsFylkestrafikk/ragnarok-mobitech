<?php

namespace Ragnarok\Mobitech\Facades;

use Illuminate\Support\Facades\Facade;
use Ragnarok\Mobitech\Services\MobitechAuthToken;

class MobitechAuth extends Facade
{
    protected static function getFacadeAccessor()
    {
        return MobitechAuthToken::class;
    }
}
