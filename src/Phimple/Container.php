<?php

/**
 * This file is part of the Phimple package.
 *
 * (c) Elliot Wright <elliot@elliotwright.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phimple;

use ArrayAccess;
use Closure;
use InvalidArgumentException;
use SplObjectStorage;

/**
 * Container
 *
 * @author Elliot Wright <elliot@elliotwright.co>
 */
class Container implements ArrayAccess, ContainerInterface
{
    /**
     * @var SplObjectStorage
     */
    protected $factories;

    /**
     * @var LockBox
     */
    protected $parameters;

    /**
     * @var LockBox
     */
    protected $services;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->factories = new SplObjectStorage();
        $this->parameters = new LockBox();
        $this->services = new LockBox();
    }

    /**
     * Array access for setting parameters
     *
     * @param  string $name
     * @param  mixed  $value
     */
    public function offsetSet($name, $value)
    {
        $this->setParameter($name, $value);
    }

    /**
     * Array access for getting parameters
     *
     * @param string $name
     *
     * @return mixed
     */
    public function offsetGet($name)
    {
        return $this->getParameter($name);
    }

    /**
     * Array item exists
     *
     * @param string $name
     *
     * @return boolean
     */
    public function offsetExists($name)
    {
        return $this->hasParameter($name);
    }

    /**
     * Array item unset
     *
     * @param string $name
     *
     * @return boolean
     */
    public function offsetUnset($name)
    {
        return $this->removeParameter($name);
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
        if (!$this->services->has($name)) {
            throw new InvalidArgumentException(sprintf('Service "%s" is not defined.', $name));
        }

        /** @var Closure $service */
        $service = $this->services->get($name);

        if ($this->services->isLocked($name)
            || !method_exists($this->services->get($name), '__invoke')) {
            return $service;
        }

        if ($this->factories->contains($service)) {
            return $service($this);
        }

        $this->services->set($name, $service($this));
        $this->services->lock($name);

        return $this->services->get($name);
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
    public function remove($name)
    {
        if ($this->services->has($name)) {
            if (is_object($this->services->get($name))) {
                unset($this->factories[$this->services->get($name)]);
            }

            $this->services->remove($name);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setParameter($name, $value)
    {
        $this->parameters->set($name, $value);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getParameter($name)
    {
        if (!$this->parameters->has($name)) {
            throw new InvalidArgumentException(sprintf('Parameter "%s" is not defined.', $name));
        }

        return $this->parameters->get($name);
    }

    /**
     * {@inheritDoc}
     */
    public function hasParameter($name)
    {
        return $this->parameters->has($name);
    }

    /**
     * {@inheritDoc}
     */
    public function removeParameter($name)
    {
        return $this->parameters->remove($name);
    }

    /**
     * All array access keys
     *
     * @return array
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
     * @throws InvalidArgumentException
     */
    public function factory($callable)
    {
        if (!is_object($callable) || !method_exists($callable, '__invoke')) {
            throw new InvalidArgumentException('Service definition is not a Closure or invokable object.');
        }

        $this->factories->attach($callable);

        return $callable;
    }

    /**
     * Extends a service definition.
     *
     * @param string   $name
     * @param callable $callable
     * @param boolean  $strict
     *
     * @return callable
     *
     * @throws InvalidArgumentException
     */
    public function extend($name, $callable, $strict = true)
    {
        if (!$this->services->has($name)) {
            if ($strict) {
                throw new InvalidArgumentException(sprintf('Service "%s" is not defined.', $name));
            } else {
                return false;
            }
        }

        $factory = $this->services->get($name);

        if (!is_object($factory) || !method_exists($factory, '__invoke')) {
            throw new InvalidArgumentException(sprintf('Service "%s" does not contain an object definition.', $name));
        }

        if (!is_object($callable) || !method_exists($callable, '__invoke')) {
            throw new InvalidArgumentException('Extension service definition is not a Closure or invokable object.');
        }

        $extended = function($c) use($callable, $factory) {
            /** @var Closure $factory */
            return $callable($factory($c), $c);
        };

        if ($this->factories->contains($factory)) {
            $this->factories->detach($factory);
            $this->factories->attach($extended);
        }

        $this->services->unlock($name);
        $this->services->set($name, $extended);

        return $this->services->get($name);
    }
}
