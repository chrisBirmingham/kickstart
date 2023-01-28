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
     * @return bool
     */
    public function invoke(callable $handler): bool
    {
        try {
            list($data, $context) = $this->getNextEvent();

            $response = ($handler)($data, $context);

            $this->sendResponse($context->getAwsRequestId(), $response);
            return true;
        } catch (\Throwable $exception) {
            $this->sendFailure($exception, (isset($context) ? $context->getAwsRequestId() : null));
            return false;
        }
    }

    /**
     * @return array
     * @throws \JsonException
     */
    protected function getNextEvent(): array
    {
        $url = "http://$this->api/$this->version/runtime/invocation/next";
        $response = $this->client->get($url);

        $context = $this->contextFactory->create(
            $response->getHeader("Lambda-Runtime-Aws-Request-Id")[0],
            (int) $response->getHeader("Lambda-Runtime-Deadline-Ms")[0],
            $response->getHeader("Lambda-Runtime-Invoked-Function-Arn")[0],
            $response->getHeader("Lambda-Runtime-Trace-Id")[0]
        );

        $data = json_decode($response->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);
        return [$data, $context];
    }

    /**
     * @param string $invocationId
     * @param string $response
     */
    protected function sendResponse(string $invocationId, mixed $response): void
    {
        $url = "http://$this->api/$this->version/runtime/invocation/$invocationId/response";
        $this->sendJson($url, $response);
    }

    /**
     * @param \Throwable $exception
     * @param ?string $invocationId
     */
    protected function sendFailure(\Throwable $exception, ?string $invocationId = null): void
    {
        if ($invocationId === null) {
            $this->initialisationFailure(exception: $exception);
        } else {
            $url = "http://$this->api/$this->version/runtime/invocation/$invocationId/error";

            $response = [
                "errorMessage" => $exception->getMessage(),
                "type" => get_class($exception),
                "stackTrace" => explode(PHP_EOL, $exception->getTraceAsString())
            ];

            $this->sendJson($url, $response);
        }
    }

    /**
     * @param string $message
     * @param ?\Throwable $exception
     */
    public function initialisationFailure(string $message = "", ?\Throwable $exception = null): void
    {
        $url = "http://$this->api/$this->version/runtime/init/error";

        $response = [
            "errorMessage" => $message . ($exception ? $exception->getMessage() : ""),
            "errorType" => "Runtime." . ($exception ? get_class($exception) : "Internal"),
            "stackTrace" => ($exception ? explode(PHP_EOL, $exception->getTraceAsString()) : [])
        ];

        $this->sendJson($url, $response);
    }

    /**
     * @param string $url
     * @param mixed $response
     * @throws \InvalidArgumentException
     */
    protected function sendJson(string $url, mixed $response): void
    {
        $response = json_encode($response);

        if ($response === false) {
            throw new \InvalidArgumentException("Response from handler couldn't be json encoded. " . json_last_error_msg());
        }

        $this->client->post($url, ["body" => $response]);
    }
}
