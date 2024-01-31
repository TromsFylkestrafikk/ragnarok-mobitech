<?php

namespace Ragnarok\Mobitech\Services;

use Ragnarok\Sink\Traits\LogPrintf;

class MobitechImporter
{
    use LogPrintf;

    public function __construct()
    {
        $this->logPrintfInit('[MobitechImporter]: ');
    }

    public function import($filename)
    {
        return $this;
    }

    public function deleteImport($filename)
    {
        return $this;
    }
}
