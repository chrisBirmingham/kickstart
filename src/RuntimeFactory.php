<?php

namespace Intermaterium\Kickstart;

use GuzzleHttp\Client;
use Intermaterium\Kickstart\Context\ContextFactory;

class RuntimeFactory
{
    const DEFAULT_VERSION = "2018-06-01";

    /**
     * @param string $api
     * @param string $version
     * @return Runtime
     */
    public function create(string $api, string $version = self::DEFAULT_VERSION): Runtime
    {
        $client = new Client();
        $contextFactory = new ContextFactory();
        return new Runtime($client, $contextFactory, $api, $version);
    }
}
