<?php

namespace Intermaterium\Kickstart\Context;

class Context
{
    /**
     * @var string
     */
    protected $awsRequestId;

    /**
     * @var int
     */
    protected $lambdaRuntimeDeadlineMs;

    /**
     * @var string
     */
    protected $invokedFunctionArn;

    /**
     * @var string
     */
    protected $lambdaRuntimeTraceId;

    /**
     * @param string $awsRequestId,
     * @param int $lambdaRuntimeDeadlineMs
     * @param string $invokedFunctionArn
     * @param string $lambdaRuntimeTraceId
     */
    public function __construct(
        string $awsRequestId,
        int $lambdaRuntimeDeadlineMs,
        string $invokedFunctionArn,
        string $lambdaRuntimeTraceId
    ) {
        $this->awsRequestId = $awsRequestId;
        $this->lambdaRuntimeDeadlineMs = $lambdaRuntimeDeadlineMs;
        $this->invokedFunctionArn = $invokedFunctionArn;
        $this->lambdaRuntimeTraceId = $lambdaRuntimeTraceId;
    }

    /**
     * @return string
     */
    public function getAwsRequestId(): string
    {
        return $this->awsRequestId;
    }

    /**
     * @return int
     */
    public function getLambdaRuntimeDeadlineMs(): int
    {
        return $this->lambdaRuntimeDeadlineMs;
    }

    /**
     * @return string
     */
    public function getInvokedFunctionArn(): string
    {
        return $this->invokedFunctionArn;
    }

    /**
     * @return string
     */
    public function getLambdaRuntimeTraceId(): string
    {
        return $this->lambdaRuntimeTraceId;
    }
}
