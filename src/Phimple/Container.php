<?php

/*
 * This file is part of the Phimple package.
 *
 * (c) Elliot Wright <elliot@elliotwright.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phimple;

use Phimple\ContainerInterface;
use Phimple\LockBox;

/**
 * Container
 *
 * @author Elliot Wright <elliot@elliotwright.co>
 */
class Container implements ContainerInterface
{
    protected $parameters;
    protected $services;
    protected $factories;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->factories = new \SplObjectStorage();
        $this->parameters = new LockBox();
        $this->services = new LockBox();
    }

    /**
     * {@inheritDoc}
     */
    public function set($name, $value)
    {
        $this->services->set($name, $value);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function get($name)
    {
        if ( ! $this->services->has($name)) {
            throw new \InvalidArgumentException(sprintf('Service "%s" is not defined.', $name));
        }

        if ($this->services->isLocked($name)
            || ! method_exists($this->services->get($name), '__invoke')) {
            return $this->services->get($name);
        }

        if (isset($this->factories[$this->services->get($name)])) {
            return $this->services->get($name)($this);
        }

        $this->services->set($name, $this->services->get($name)($this));
        $this->services->lock($name);

        return $this->services->get($name)($this);
    }

    /**
     * {@inheritDoc}
     */
    public function has($name)
    {
        return $this->services->has($name);
    }

    /**
     * {@inheritDoc}
     */
    public function unset($name)
    {
        unset($this->factories[$name]);

        return $this->services->remove($name);
    }

    /**
     * {@inheritDoc}
     */
    public function setParam($name, $value)
    {
        $this->parameters->set($name, $value);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getParam($name)
    {
        return $this->parameters->get($name);
    }

    /**
     * {@inheritDoc}
     */
    public function hasParam($name)
    {
        return $this->parameters->has($name);
    }

    /**
     * {@inheritDoc}
     */
    public function unsetParam($name)
    {
        return $this->parameters->remove($name);
    }

    /**
     * {@inheritDoc}
     */
    public function keys()
    {
        return $this->services->contents();
    }

    /**
     * Marks a callable as being a factory service.
     *
     * @param callable $callable
     *
     * @return callable
     *
     * @throws \InvalidArgumentException
     */
    public function factory($callable)
    {
        if ( ! is_object($callable) || ! method_exists($callable, '__invoke')) {
            throw \InvalidArgumentException('Service definition is not a Closure or invokable object.');
        }

        $this->factories->attach($callable);

        return $callable;
    }
}
