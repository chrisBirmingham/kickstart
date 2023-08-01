<?php

namespace Intermaterium\Kickstart\Response;

class ErrorResponseBuilder
{
    const TYPE_INIT = 'Init';

    const TYPE_RUNTIME = 'Runtime';

    /**
     * @param \Throwable $throwable
     * @param string $type
     * @param string $message
     * @return array
     */
    public function build(\Throwable $throwable, string $type, string $message = ''): array
    {
        $response = [];

        $message = (empty($message) ? '' : "$message. Reason: ");
        $response['errorMessage'] = $message . $throwable->getMessage();

        if ($throwable->getPrevious()) {
            $response['previousErrors'] = $this->getPreviousExceptionMessages($throwable);
        }

        $response['errorType'] = "$type." . $this->getExceptionName($throwable);
        $response['stackTrace'] = $throwable->getTrace();


        return $response;
    }

    /**
     * @param \Throwable $throwable
     * @return string[]
     */
    protected function getPreviousExceptionMessages(\Throwable $throwable): array
    {
        $messages = [];

        while (($throwable = $throwable->getPrevious()) !== null) {
            $messages[] = $throwable->getMessage();
        }

        return $messages;
    }

    /**
     * @param \Throwable $throwable
     * @return string
     */
    protected function getExceptionName(\Throwable $throwable): string
    {
        $className = $throwable::class;

        if (str_contains($className, "\\")) {
            $className = substr($className, strrpos($className, "\\") + 1);
        }

        return $className;
    }
}
