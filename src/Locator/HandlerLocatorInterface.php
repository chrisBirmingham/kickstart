<?php 

namespace Intermaterium\Kickstart\Locator;

use Intermaterium\Kickstart\Locator\Exception\InvalidHandlerException;
use Intermaterium\Kickstart\Locator\Exception\UnknownHandlerException;

interface HandlerLocatorInterface
{
    /**
     * @param string $id
     * @return callable
     * @throws InvalidHandlerException
     * @throws UnknownHandlerException
     */
    public function get(string $id): callable;
}
