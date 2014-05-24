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

use Phimple\Exception\ItemNotFoundException;
use Phimple\Exception\LockedItemException;

/**
 * LockBox stores items, and can lock them on request.
 *
 * @author Elliot Wright <elliot@elliotwright.co>
 */
class LockBox implements \Countable
{
    protected $items;
    protected $locked = [];

    /**
     * Constructor.
     *
     * @param array $items
     */
    public function __construct(array $items = array())
    {
        $this->items = $items;
    }

    /**
     * Sets a item by name.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return LockBox
     */
    public function set($name, $value)
    {
        if (isset($this->locked[$name])) {
            throw new LockedItemException($name);
        }

        $this->items[$name] = $value;
    }

    /**
     * Returns a item by name.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function get($name)
    {
        if ( ! $this->has($name)) {
            throw new ItemNotFoundException($name);
        }

        return $this->items[$name];
    }

    /**
     * Returns true if this box contains the item.
     *
     * @param string  $name
     *
     * @return boolean
     */
    public function has($name)
    {
        return array_key_exists($name, $this->items);
    }

    /**
     * Removes a item.
     *
     * @param string $name
     *
     * @return LockBox
     */
    public function remove($name)
    {
        unset($this->items[$name], $this->locked[$name]);

        return $this;
    }

    /**
     * Locks an item.
     *
     * @param string $name
     *
     * @return LockBox
     */
    public function lock($name)
    {
        if ( ! $this->has($name)) {
            throw new ItemNotFoundException($name);
        }

        $this->locked[$name] = true;

        return $this;
    }

    /**
     * Unlocks an item.
     *
     * @param string $name
     *
     * @return LockBox
     */
    public function unlock($name)
    {
        if ( ! $this->has($name)) {
            throw new ItemNotFoundException($name);
        }

        unset($this->locked[$name]);

        return $this;
    }

    /**
     * Returns true if the item is locked.
     *
     * @param string $name
     *
     * @return boolean
     */
    public function isLocked($name)
    {
        if ( ! $this->has($name)) {
            throw new ItemNotFoundException($name);
        }

        return isset($this->locked[$name]);
    }

    /**
     * Returns the number of parameters.
     *
     * @return int The number of parameters
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Returns the items names.
     *
     * @return array
     */
    public function contents()
    {
        return array_keys($this->items);
    }
}
