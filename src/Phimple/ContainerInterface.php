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

/**
 * Container Interface
 *
 * @author Elliot Wright <elliot@elliotwright.co>
 */
interface ContainerInterface
{
    /**
     * Set item in container
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     */
    public function set($name, $value);

    /**
     * Get item in container
     *
     * @param string $name
     *
     * @return mixed
     */
    public function get($name);

    /**
     * Check if item is in container
     *
     * @param string $name
     *
     * @return boolean
     */
    public function has($name);

    /**
     * Remove item from the container
     *
     * @param string $name
     *
     * @return mixed
     */
    public function remove($name);

    /**
     * Set a parameter
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     */
    public function setParameter($name, $value);

    /**
     * Get a parameter
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParameter($name);

    /**
     * Check if parameter exists
     *
     * @param string $name
     *
     * @return mixed
     */
    public function hasParameter($name);

    /**
     * Remove a parameter
     *
     * @param $name
     *
     * @return mixed
     */
    public function removeParameter($name);
}
