<?php

namespace Ragnarok\Mobitech\Services;

use Illuminate\Support\Facades\Http;
use Ragnarok\Mobitech\Facades\MobitechAuth;
use Ragnarok\Sink\Traits\LogPrintf;

class MobitechFiles
{
    use LogPrintf;

    /**
     * External folders to download data from.
     *
     * @var array
     */
    protected $folders = [
        // Norled
        '101010/ferry-statistics/%s',
        '101010/orca-transactions/%s',
        '101010/statistics/legs/%s',

        // Torghatten Nord
        '101677/ferry-statistics/%s',
        '101677/statistics/legs/%s',

        // Boreal
        '101678/ferry-statistics/%s',
        '101678/orca-transactions/%s',
        '101678/statistics/legs/%s',
    ];

    public function __construct()
    {
        $this->logPrintfInit('[MobitechService]: ');
    }

    /**
     * Fetching data from Mobitech's via their Data Lake Gen 2 API.
     *
     * @param string $id Chunk ID. Date on format YYYY-MM-DD
     *
     * @return array
     */
    public function getData(string $id)
    {
        $this->debug('Fetching Mobitech data with id %s...', $id);
        $data = [];
        $total = 0;
        foreach ($this->folders as $folder) {
            // Get external file list.
            $dir = sprintf($folder, $id);
            $url = sprintf(config('ragnarok_mobitech.file_list_url'), $dir);
            $result = Http::withToken(MobitechAuth::getApiToken())->get($url)->json();
            if (!isset($result['paths'])) {
                $this->warning('No files found in external folder %s', $dir);
                continue;
            }

            // Download all files.
            $fileCount = 0;
            foreach ($result['paths'] as $fileInfo) {
                $url = sprintf(config('ragnarok_mobitech.download_url'), $fileInfo['name']);
                $content = Http::withToken(MobitechAuth::getApiToken())->get($url)->body();

                // Constructing a human readable filename for the content.
                $pathName = str_replace('/', '_', $folder);
                $filename = sprintf($pathName, basename($fileInfo['name']));
                $data[$filename] = $content;
                $fileCount += 1;
            }
            $total += $fileCount;
            $this->debug('Downloaded %d file(s) from %s', $fileCount, $dir);
        }
        $this->debug('Total: %d file(s)', $total);
        return $data;
    }
}
