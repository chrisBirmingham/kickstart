<?php

namespace Intermaterium\Kickstart\Context;

class ContextFactory
{
    /**
     * @param string $awsRequestId,
     * @param int $lambdaRuntimeDeadlineMs
     * @param string $invokedFunctionArn
     * @param string $lambdaRuntimeTraceId
     * @return Context
     */
    public function create(
        string $awsRequestId,
        int $lambdaRuntimeDeadlineMs,
        string $invokedFunctionArn,
        string $lambdaRuntimeTraceId
    ): Context {
        return new Context(
            $awsRequestId,
            $lambdaRuntimeDeadlineMs,
            $invokedFunctionArn,
            $lambdaRuntimeTraceId
        );
    }
}
