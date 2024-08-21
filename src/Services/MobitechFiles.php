<?php

namespace Ragnarok\Mobitech\Services;

use Archive7z\Archive7z;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Ragnarok\Mobitech\Facades\MobitechAuth;
use Ragnarok\Sink\Traits\LogPrintf;

class MobitechFiles
{
    use LogPrintf;

    /**
     * @var Filesystem
     */
    protected $tmpDisk;

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
     * Fetching data from Mobitech via their Data Lake Gen 2 API.
     *
     * @param string $id Chunk ID. Date on format YYYY-MM-DD
     *
     * @return array
     */
    public function getData(string $id)
    {
        $this->debug('Fetching Mobitech data with id %s...', $id);
        $this->checkExpirationDate();

        // Trying primary download method first.
        $data = $this->getZipData($id);
        $total = count($data);
        if ($total > 0) {
            // ZIP file content was found and collected.
            return $data;
        }

        // Secondary download method: Downloading individual files is very slow
        // due to the high number of small files.
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

    /**
     * Primary download method: Downloading a single ZIP file containing all
     * transactions and statistics for the given date, but this archive has
     * a deep directory structure and therefore needs to be extracted and
     * flattened with modified filenames.
     *
     * @param string $id Chunk ID. Date on format YYYY-MM-DD
     *
     * @return array Extracted file content. Will be empty if error occurs.
     */
    public function getZipData(string $id): array
    {
        $data = [];
        $path = sprintf('by-date-zipped/%s.zip', $id);
        $url = sprintf(config('ragnarok_mobitech.download_url'), $path);
        $response = Http::withToken(MobitechAuth::getApiToken())->get($url);
        if ($response->successful()) {
            // Store zip file in temporary folder.
            $outputDir = uniqid("mobitech-{$id}");
            $this->getDisk()->makeDirectory($outputDir);
            $zipFile = sprintf('%s/%s.zip', $outputDir, $id);
            $this->getDisk()->put($zipFile, $response->body());

            // Extract zip file.
            $this->debug('Extracting ZIP file (%s)...', basename($path));
            $archive = new Archive7z($this->getDisk()->path($zipFile));
            if (!$archive->isValid()) {
                $this->error('Invalid archive! Trying secondary download method instead...');
                $this->getDisk()->deleteDirectory($outputDir);
                return [];
            }
            $archive->setOutputDirectory($this->getDisk()->path($outputDir))->extract();

            // Collect content from extracted files.
            $total = 0;
            foreach ($this->folders as $folder) {
                $dir = sprintf('%s/by-date/%s/%s', $outputDir, $id, rtrim($folder, '%s'));
                $files = $this->getDisk()->files($dir);
                $fileCount = count($files);
                foreach ($files as $filepath) {
                    $content = $this->getDisk()->get($filepath);

                    // Constructing a human readable filename for the content.
                    $pathName = str_replace('/', '_', $folder);
                    $filename = sprintf($pathName, basename($filepath));
                    $data[$filename] = $content;
                }
                if ($fileCount > 0) {
                    $total += $fileCount;
                    $this->debug('Extracted %d file(s) from %s', $fileCount, sprintf($folder, null));
                }
            }
            $this->debug('Total: %d file(s)', $total);
            $this->getDisk()->deleteDirectory($outputDir);
        }
        return $data;
    }

    /**
     * @return Filesystem Temporary local storage.
     */
    public function getDisk()
    {
        if (!$this->tmpDisk) {
            $this->tmpDisk = Storage::disk(config('ragnarok_mobitech.tmp_disk'));
        }
        return $this->tmpDisk;
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
