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
    public function destinationTables(): array
    {
        return [
            'mobitech_actors' => 'Actor/operator names with ID from other Mobitech tables',
            'mobitech_softpay_transactions' => 'Softpay transactions from Mobitech',
            'mobitech_statistics' => 'Passenger statistics from Mobitech',
            'mobitech_transactions' => 'AutoPass transactions from Mobitech/ORCA',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFromDate(): Carbon
    {
        return new Carbon('2023-12-20');
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
        if ($id > '2024-08-05') {
            return MobitechFiles::getChunkAsZip(static::$id, $id);
        }
        $archive = new ChunkArchive(static::$id, $id);
        foreach (MobitechFiles::getChunkAsFiles($id) as $filename => $content) {
            $archive->addFromString($filename, $content);
        }
        return $archive->save()->getFile();
    }

    /**
     * @inheritdoc
     */
    public function import(string $id, SinkFile $file): int
    {
        // Delete existing import for this date, if any.
        MobitechImporter::deleteImport($id);

        // Import data from chunk file.
        $count = 0;
        $extractor = new ChunkExtractor(static::$id, $file);
        foreach ($extractor->getFiles(true) as $filepath) {
            $count += MobitechImporter::import($id, $filepath);
        }
        return $count;
    }

    /**
     * @inheritdoc
     */
    public function deleteImport(string $id, SinkFile $file): bool
    {
        MobitechImporter::deleteImport($id);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function filenameToChunkId(string $filename): string|null
    {
        $matches = [];
        $hits = preg_match('|(?P<date>\d{4}-\d{2}-\d{2})\.zip$|', $filename, $matches);
        return $hits ? $matches['date'] : null;
    }
}
