<?php

namespace Intermaterium\Kickstart;

use GuzzleHttp\Client;
use Intermaterium\Kickstart\Context\ContextFactory;
use Intermaterium\Kickstart\Response\ErrorResponseBuilder;

class RuntimeFactory
{
    const DEFAULT_VERSION = '2018-06-01';

    protected ErrorResponseBuilder $errorResponseBuilder;

    /**
     * @param ErrorResponseBuilder $errorResponseBuilder
     */
    public function __construct(ErrorResponseBuilder $errorResponseBuilder)
    {
        $this->errorResponseBuilder = $errorResponseBuilder;
    }

    /**
     * @param string $api
     * @param string $version
     * @return Runtime
     */
    public function create(string $api, string $version = self::DEFAULT_VERSION): Runtime
    {
        $client = new Client();
        $contextFactory = new ContextFactory();
        return new Runtime($client, $contextFactory, $this->errorResponseBuilder, $api, $version);
    }
}
