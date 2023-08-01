<?php

namespace Intermaterium\KickStart\Test\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Intermaterium\Kickstart\Context\Context;
use Intermaterium\Kickstart\Context\ContextFactory;
use Intermaterium\Kickstart\Response\ErrorResponseBuilder;
use Intermaterium\Kickstart\Runtime;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class RuntimeTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected \Mockery\MockInterface|\Mockery\LegacyMockInterface|Client $client;

    protected \Mockery\MockInterface|\Mockery\LegacyMockInterface|ContextFactory $contextFactory;

    protected \Mockery\MockInterface|\Mockery\LegacyMockInterface|ErrorResponseBuilder $errorResponseBuilder;

    protected string $api = 'localhost';

    protected string $version = '2018';

    public function setUp(): void
    {
        $this->client = Mockery::mock(Client::class);
        $this->contextFactory = Mockery::mock(ContextFactory::class);
        $this->errorResponseBuilder = Mockery::mock(ErrorResponseBuilder::class);
    }

    public function testCallout(): void
    {
        $id = 'this is an id';
        $ms = 500000;
        $funcArn = 'this is a func';
        $traceId = 'this is another id';
        $body = '{"data": "test data"}';
        $postData = 'Hello world';

        $handler = function(array $data, Context $context) use ($body, $postData): string {
            $this->assertSame(json_decode($body, true), $data);
            return $postData;
        };

        $context = Mockery::mock(Context::class);
        $context
            ->shouldReceive('getAwsRequestId')
            ->andReturn($id);

        $this->contextFactory
            ->shouldReceive('create')
            ->with($id, $ms, $funcArn, $traceId)
            ->andReturn($context)
            ->once();

        $getResponse = Mockery::mock(ResponseInterface::class);
        $getResponse
            ->shouldReceive('getHeader')
            ->andReturn([$id], [$ms], [$funcArn], [$traceId])
            ->times(4);

        $getResponse
            ->shouldReceive('getBody->getContents')
            ->andReturn($body)
            ->once();

        $this->client
            ->shouldReceive('get')
            ->andReturn($getResponse)
            ->once();

        $this->client
            ->shouldReceive('post')
            ->with(Mockery::type('string'), ['body' => json_encode($postData)])
            ->once();

        $runtime = new Runtime(
            $this->client,
            $this->contextFactory,
            $this->errorResponseBuilder,
            $this->api,
            $this->version
        );

        $runtime->invoke($handler);
    }

    public function testFailedApiCall(): void
    {
        $expectedUrl = "http://$this->api/$this->version/runtime/init/error";

        $handler = function(array $data, Context $context): void {};

        $exception = Mockery::mock(RequestException::class);

        $this->client
            ->shouldReceive('get')
            ->andThrow($exception)
            ->once();

        $this->client
            ->shouldReceive('post')
            ->with($expectedUrl, Mockery::type('array'))
            ->once();

        $this->errorResponseBuilder
            ->shouldReceive('build')
            ->with($exception, ErrorResponseBuilder::TYPE_INIT, Mockery::type('string'))
            ->andReturn([])
            ->once();

        $runtime = new Runtime(
            $this->client,
            $this->contextFactory,
            $this->errorResponseBuilder,
            $this->api,
            $this->version
        );

        $runtime->invoke($handler);
    }

    public function testHandlerReturnedFailure(): void
    {
        $id = 'this is an id';
        $exception = Mockery::mock(\Exception::class);

        $expectedUrl = "http://$this->api/$this->version/runtime/invocation/$id/error";

        $handler = function(array $data, Context $context) use ($exception): mixed {
            throw $exception;
        };

        $context = Mockery::mock(Context::class);
        $context
            ->shouldReceive('getAwsRequestId')
            ->andReturn($id);

        $this->contextFactory
            ->shouldReceive('create')
            ->andReturn($context)
            ->once();

        $getResponse = Mockery::mock(ResponseInterface::class);
        $getResponse
            ->shouldReceive('getHeader')
            ->andReturn([$id], [''])
            ->times(4);

        $getResponse
            ->shouldReceive('getBody->getContents')
            ->andReturn('{"data": "Hello world"}')
            ->once();

        $this->client
            ->shouldReceive('get')
            ->andReturn($getResponse)
            ->once();

        $this->client
            ->shouldReceive('post')
            ->with($expectedUrl, Mockery::type('array'))
            ->once();

        $this->errorResponseBuilder
            ->shouldReceive('build')
            ->with($exception, ErrorResponseBuilder::TYPE_RUNTIME)
            ->andReturn([])
            ->once();

        $runtime = new Runtime(
            $this->client,
            $this->contextFactory,
            $this->errorResponseBuilder,
            $this->api,
            $this->version
        );

        $runtime->invoke($handler);
    }
}
