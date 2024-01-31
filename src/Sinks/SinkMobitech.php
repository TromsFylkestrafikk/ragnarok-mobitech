<?php

namespace Ragnarok\Mobitech\Sinks;

use Illuminate\Support\Carbon;
use Ragnarok\Mobitech\Facades\MobitechFiles;
use Ragnarok\Mobitech\Facades\MobitechImporter;
use Ragnarok\Sink\Models\SinkFile;
use Ragnarok\Sink\Services\ChunkArchive;
use Ragnarok\Sink\Services\ChunkExtractor;
use Ragnarok\Sink\Sinks\SinkBase;

class SinkMobitech extends SinkBase
{
    public static $id = "mobitech";
    public static $title = "Mobitech";

    /**
     * @inheritdoc
     */
    public function getFromDate(): Carbon
    {
        return new Carbon('2023-12-11');
    }

    /**
     * @inheritdoc
     */
    public function getToDate(): Carbon
    {
        return today()->subDay();
    }

    /**
     * @inheritdoc
     */
    public function fetch(string $id): SinkFile|null
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function import(string $id, SinkFile $file): int
    {
        return 0;
    }

    /**
     * @inheritdoc
     */
    public function deleteImport(string $chunkId): bool
    {
        return true;
    }
}
