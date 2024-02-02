<?php

namespace Ragnarok\Mobitech\Services;

use Illuminate\Support\Facades\Http;
use Ragnarok\Sink\Traits\LogPrintf;

class MobitechAuthToken
{
    use LogPrintf;

    /**
     * Auth token received from Mobitech.
     *
     * @var string|null
     */
    protected $apiToken = null;

    /**
     * Timer used to track age of api token.
     *
     * @var int
     */
    protected $tokenExpires = 0;

    public function __construct()
    {
        $this->logPrintfInit('[Mobitech Token] ');
    }

    public function getApiToken()
    {
        $this->requestToken();
        return $this->apiToken;
    }

    protected function requestToken()
    {
        if ($this->apiToken && time() < $this->tokenExpires) {
            // Token is still valid.
            return;
        }
        $this->debug('Requesting API token...');
        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
            'Cache-Control' => 'no-cache',
        ])->asForm()->post(config('ragnarok_mobitech.token_endpoint'), [
            'client_id' => config('ragnarok_mobitech.client_id'),
            'client_secret' => config('ragnarok_mobitech.client_secret'),
            'grant_type' => 'client_credentials',
            'scope' => config('ragnarok_mobitech.scope'),
        ]);

        $result = $response->json();
        $this->apiToken = $result['access_token'];
        $tokenLifetime = $result['expires_in'];
        $this->tokenExpires = time() + intval($tokenLifetime);
        $this->debug('Token received. Expires in %d minutes', $tokenLifetime / 60);
    }
}
