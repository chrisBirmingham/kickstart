<?php

namespace Intermaterium\Kickstart\Context;

class Context
{
    /**
     * @var string
     */
    protected $awsRequestId;

    /**
     * @var string
     */
    protected $invokedFunctionArn;

    /**
     * @param string $awsRequestId,
     * @param string $invokedFunctionArn
     */
    public function __construct(
        string $awsRequestId,
        string $invokedFunctionArn
    ) {
        $this->awsRequestId = $awsRequestId;
        $this->invokedFunctionArn = $invokedFunctionArn;
    }

    /**
     * @return string
     */
    public function getAwsRequestId(): string
    {
        return $this->awsRequestId;
    }

    /**
     * return string
     */
    public function getInvokedFunctionArn(): string
    {
        return $this->invokedFunctionArn;
    }
}
