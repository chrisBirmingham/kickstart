<?php

namespace Intermaterium\Kickstart;

use GuzzleHttp\Client;
use Intermaterium\Kickstart\Context\ContextFactory;

class Runtime
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ContextFactory
     */
    protected $contextFactory;

    /**
     * @var string
     */
    protected $api;

    /**
     * @var string
     */
    protected $version;

    /**
     * @param Client $client
     * @param ContextFactory $contextFactory
     * @param string $api
     * @param string $version
     */
    public function __construct(
        Client $client,
        ContextFactory $contextFactory,
        string $api,
        string $version
    ) {
        $this->client = $client;
        $this->contextFactory = $contextFactory;
        $this->api = $api;
        $this->version = $version;
    }

    /**
     * @param callable $handler
     */
    public function invoke(callable $handler): void
    {
        list($data, $context) = $this->getNextEvent();

        $response = call_user_func($handler, $data, $context);

        $this->sendResponse($context->getAwsRequestId(), json_encode($response));
    }

    /**
     * @return array
     * @throws JsonException
     */
    protected function getNextEvent(): array
    {
        $url = "http://$this->api/$this->version/runtime/invocation/next";
        $response = $this->client->get($url);

        $context = $this->contextFactory->create(
            $response->getHeader("lambda-runtime-aws-request-id")[0],
            $response->getHeader("lambda-runtime-invoked-function-arn")[0]
        );

        $data = json_decode($response->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);
        return [$data, $context];
    }

    /**
     * @param string $invocationId
     * @param string $response
     */
    protected function sendResponse(string $invocationId, string $response): void
    {
        $url = "http://$this->api/$this->version/runtime/invocation/$invocationId/response";
        $this->client->post($url, [
            "body" => $response
        ]);
    }
}
