<?php

namespace Intermaterium\Kickstart;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Intermaterium\Kickstart\Context\ContextFactory;

class Runtime
{
    protected Client $client;

    protected ContextFactory $contextFactory;

    protected string $api;

    protected string $version;

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
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function invoke(callable $handler): bool
    {
        try {
            list($data, $context) = $this->getNextEvent();
        } catch (GuzzleException|\JsonException $e) {
            $this->initialisationFailure('Encountered error while trying to retrieve lambda event', $e);
            return false;
        }

        try {
            $response = ($handler)($data, $context);

            $this->sendResponse($context->getAwsRequestId(), $response);
            return true;
        } catch (\Throwable $exception) {
            $this->sendFailure($exception, $context->getAwsRequestId());
            return false;
        }
    }

    /**
     * @return array
     * @throws \JsonException
     * @throws GuzzleException
     */
    protected function getNextEvent(): array
    {
        $url = "http://$this->api/$this->version/runtime/invocation/next";
        $response = $this->client->get($url);

        $context = $this->contextFactory->create(
            $response->getHeader("Lambda-Runtime-Aws-Request-Id")[0],
            (int) ($response->getHeader("Lambda-Runtime-Deadline-Ms")[0] ?? 0),
            $response->getHeader("Lambda-Runtime-Invoked-Function-Arn")[0] ?? "",
            $response->getHeader("Lambda-Runtime-Trace-Id")[0] ?? ""
        );

        $data = json_decode($response->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);
        return [$data, $context];
    }

    /**
     * @param string $invocationId
     * @param string $response
     * @throws GuzzleException
     */
    protected function sendResponse(string $invocationId, mixed $response): void
    {
        $url = "http://$this->api/$this->version/runtime/invocation/$invocationId/response";
        $this->sendJson($url, $response);
    }

    /**
     * @param \Throwable $exception
     * @param string $invocationId
     * @throws GuzzleException
     */
    protected function sendFailure(\Throwable $exception, string $invocationId): void
    {
        $url = "http://$this->api/$this->version/runtime/invocation/$invocationId/error";

        $response = [
            "errorMessage" => $exception->getMessage(),
            "type" => "Runtime." . $exception::class,
            "stackTrace" => $exception->getTraceAsString(),
            "previousErrors" => $this->getPreviousExceptionMessages($exception)
        ];

        $this->sendJson($url, $response);

    }

    /**
     * @param string $message
     * @param \Exception $exception
     * @throws GuzzleException
     */
    public function initialisationFailure(string $message, \Exception $exception): void
    {
        $url = "http://$this->api/$this->version/runtime/init/error";

        $response = [
            "errorMessage" => "$message. Reason: " . $exception->getMessage(),
            "errorType" => "Init." . $exception::class,
            "stackTrace" => $exception->getTrace(),
            "previousErrors" => $this->getPreviousExceptionMessages($exception)
        ];

        $this->sendJson($url, $response);
    }

    /**
     * @param \Throwable $exception
     * @return string[]
     */
    protected function getPreviousExceptionMessages(\Throwable $exception): array
    {
        $messages = [];

        while (($exception = $exception->getPrevious()) !== null) {
            $messages[] = $exception->getMessage();
        }

        return $messages;
    }

    /**
     * @param string $url
     * @param mixed $response
     * @throws \InvalidArgumentException
     * @throws GuzzleException
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
