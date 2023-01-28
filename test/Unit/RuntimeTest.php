<?php

namespace Intermaterium\Kickstart\Test\Unit;

use GuzzleHttp\Client;
use Intermaterium\Kickstart\Context\Context;
use Intermaterium\Kickstart\Context\ContextFactory;
use Intermaterium\Kickstart\Runtime;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class RuntimeTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testCallout(): void
    {
        $id = "this is an id";
        $funcArn = "this is a func";
        $body = '{"data": "test data"}';
        $postData = "Hello world";

        $handler = function(array $data, Context $context) use ($body, $postData): mixed {
            $this->assertSame(json_decode($body, true), $data);
            return $postData;
        };

        $context = Mockery::mock(Context::class);
        $context
            ->shouldReceive("getAwsRequestId")
            ->andReturn($id);

        $contextFactory = Mockery::mock(ContextFactory::class);
        $contextFactory
            ->shouldReceive("create")
            ->with($id, $funcArn)
            ->andReturn($context)
            ->once();

        $getResponse = Mockery::mock(ResponseInterface::class);
        $getResponse
            ->shouldReceive("getHeader")
            ->with("lambda-runtime-aws-request-id")
            ->andReturn([$id])
            ->once();

        $getResponse
            ->shouldReceive("getHeader")
            ->with("lambda-runtime-invoked-function-arn")
            ->andReturn([$funcArn])
            ->once();

        $getResponse
            ->shouldReceive("getBody->getContents")
            ->andReturn($body)
            ->once();

        $client = Mockery::mock(Client::class);

        $client
            ->shouldReceive("get")
            ->andReturn($getResponse)
            ->once();

        $client
            ->shouldReceive("post")
            ->with(Mockery::type("string"), ["body" => json_encode($postData)])
            ->once();

        $runtime = new Runtime($client, $contextFactory, "", "");

        $runtime->invoke($handler);
    }
}
