<?php

namespace Yo;

use GuzzleHttp\Client;

class RequestHelper
{
    private $client;

    /**
     * Constructor.
     * @param string $baseURI You site URL
     */
    public function __construct($baseURI, $headers = null)
    {
        $this->client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $baseURI,
            'headers' => $headers
        ]);
    }

    public function get($url)
    {
        return json_decode((String)$this->client->get($url)->getBody(), true);
    }
}
