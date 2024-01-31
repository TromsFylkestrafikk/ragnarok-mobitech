<?php

namespace Ragnarok\Mobitech\Services;

use Ragnarok\Sink\Traits\LogPrintf;

class MobitechFiles
{
    use LogPrintf;

    public function __construct()
    {
        $this->logPrintfInit('[MobitechService]: ');
    }
}
