<?php

namespace Ragnarok\Mobitech\Services;

use Illuminate\Support\Facades\Http;
use Ragnarok\Mobitech\Facades\MobitechAuth;
use Ragnarok\Sink\Traits\LogPrintf;

class MobitechFiles
{
    use LogPrintf;

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

        // Get external file list.
        $url = sprintf(config('ragnarok_mobitech.file_list_url'), $id);
        $result = Http::withToken(MobitechAuth::getApiToken())->get($url)->json();
        if (!isset($result['paths'])) {
            $this->warning('No transactions available for %s', $id);
            return [];
        }

        // Download all files.
        $data = [];
        $fileCount = 0;
        foreach ($result['paths'] as $fileInfo) {
            $url = sprintf(config('ragnarok_mobitech.download_url'), $fileInfo['name']);
            $content = Http::withToken(MobitechAuth::getApiToken())->get($url)->body();
            $data[basename($fileInfo['name'])] = $content;
            $fileCount += 1;
        }
        $this->debug('Downloaded %d file(s).', $fileCount);
        return $data;
    }
}
