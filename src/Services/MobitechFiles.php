<?php

namespace Ragnarok\Mobitech\Services;

use Illuminate\Support\Facades\Http;
use Ragnarok\Mobitech\Facades\MobitechAuth;
use Ragnarok\Sink\Services\LocalFile;
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
        '101010/softpay-transactions/%s',
        '101010/statistics/legs/%s',

        // Torghatten Nord
        '101677/ferry-statistics/%s',
        '101677/orca-transactions/%s',
        '101677/softpay-transactions/%s',
        '101677/statistics/legs/%s',

        // Boreal
        '101678/ferry-statistics/%s',
        '101678/orca-transactions/%s',
        '101678/softpay-transactions/%s',
        '101678/statistics/legs/%s',
    ];

    public function __construct()
    {
        $this->logPrintfInit('[MobitechService]: ');
    }

    /**
     * Fetching data from Mobitech as a single ZIP file.
     *
     * @param string $sinkId Sink ID.
     * @param string $chunkId Chunk ID. Date on format YYYY-MM-DD.
     *
     * @return SinkFile|null
     */
    public function getChunkAsZip(string $sinkId, string $chunkId)
    {
        $this->debug('Fetching Mobitech data (ZIP file) with id %s...', $chunkId);
        $this->checkExpirationDate();
        $filename = sprintf('%s.zip', $chunkId);
        $content = $this->downloadZipFile($filename);
        if (!$content) return null;
        $local = LocalFile::find($sinkId, $filename);
        if (!$local) {
            $local = LocalFile::createFromFilename($sinkId, $filename);
        }
        $local->put($content);
        return $local->getFile();
    }

    /**
     * @param string $filename ZIP filename.
     *
     * @return string|null ZIP file content.
     */
    protected function downloadZipFile(string $filename)
    {
        $path = sprintf('by-date-zipped/%s', $filename);
        $url = sprintf(config('ragnarok_mobitech.download_url'), $path);
        $response = Http::withToken(MobitechAuth::getApiToken())->get($url);
        return $response->successful() ? $response->body() : null;
    }

    /**
     * Fetching data from Mobitech as individual files. This is a very slow
     * download method due to the high number of small files.
     *
     * @param string $id Chunk ID. Date on format YYYY-MM-DD.
     *
     * @return array File content, keyed by filename.
     */
    public function getChunkAsFiles(string $id)
    {
        $this->debug('Fetching Mobitech data (individual files) with id %s...', $id);
        $this->checkExpirationDate();
        $data = [];
        $total = 0;
        foreach ($this->folders as $folder) {
            // Get external file list.
            $dir = sprintf($folder, $id);
            $url = sprintf(config('ragnarok_mobitech.file_list_url'), $dir);
            $result = Http::withToken(MobitechAuth::getApiToken())->get($url)->json();
            if (!isset($result['paths'])) {
                if (strpos($dir, 'orca-transactions') !== false) {
                    $this->notice('No files found in external folder %s', $dir);
                }
                continue;
            }

            // Download all files.
            $fileCount = count($result['paths']);
            foreach ($result['paths'] as $fileInfo) {
                set_time_limit(60);
                $url = sprintf(config('ragnarok_mobitech.download_url'), $fileInfo['name']);
                $content = Http::withToken(MobitechAuth::getApiToken())->get($url)->body();

                // Constructing a human readable filename for the content.
                $pathName = str_replace('/', '_', $folder);
                $filename = sprintf($pathName, basename($fileInfo['name']));
                $data[$filename] = $content;
            }
            $total += $fileCount;
            $this->debug('Downloaded %d file(s) from %s', $fileCount, $dir);
        }
        $this->debug('Total: %d file(s)', $total);
        return $data;
    }

    protected function checkExpirationDate()
    {
        $today = date('Y-m-d');
        $expDate = config('ragnarok_mobitech.expiration_date');
        $warnDate = date('Y-m-d', strtotime("$expDate -10 days"));
        if ($today < $warnDate) return;
        if ($today > $expDate) {
            $this->error('User credentials expired on %s!', $expDate);
        } else {
            $this->warning('User credentials will expire on %s.', $expDate);
        }
    }
}
