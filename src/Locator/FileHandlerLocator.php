<?php

namespace Intermaterium\Kickstart\Locator;

use Intermaterium\Kickstart\Locator\Exception\InvalidHandlerException;
use Intermaterium\Kickstart\Locator\Exception\UnknownHandlerException;

class FileHandlerLocator implements HandlerLocatorInterface
{
    /**
     * @var callable[]
     */
    protected array $handlers = [];

    protected string $root;

    /**
     * @param string $root
     */
    public function __construct(string $root)
    {
        $this->root = $root;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $id): callable
    {
        $path = "$this->root/$id.php";

        if (isset($this->handlers[$path])) {
            return $this->handlers[$path];
        }

        if (!file_exists($path)) {
            throw new UnknownHandlerException("Unknown handler with id $id");
        }

        $handler = require_once($path);

        if (!is_callable($handler)) {
            throw new InvalidHandlerException("Handler $id did not return a valid callable");
        }

        $this->handlers[$path] = $handler;
        return $handler;
    }
}
