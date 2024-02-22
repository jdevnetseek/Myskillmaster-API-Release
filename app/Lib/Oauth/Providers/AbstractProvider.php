<?php

namespace App\Lib\Oauth\Providers;

use GuzzleHttp\Client;
use Lib\Oauth\Contracts\Provider;

abstract class AbstractProvider
{
    protected $client;

    /**
     * Initiate new GuzzleHttp\Client
     */
    public function __construct()
    {
        $this->client = new Client([
            'headers' => [
                'Accept' => 'application/json'
            ],
        ]);
    }

    /**
     * Returns the current Client instance
     *
     * @return Client
     */
    protected function getHttpClient(): Client
    {
        return $this->client;
    }
}
