<?php

namespace Intermaterium\Kickstart\Context;

class ContextFactory
{
    /**
     * @param string $awsRequestId,
     * @param string $invokedFunctionArn
     * @return Context
     */
    public function create(
        string $awsRequestId,
        string $invokedFunctionArn
    ): Context {
        return new Context($awsRequestId, $invokedFunctionArn);
    }
}
