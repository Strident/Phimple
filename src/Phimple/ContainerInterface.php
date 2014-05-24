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
    public function set($name, $value);
    public function get($name);
    public function has($name);
    public function remove($name);
    public function setParameter($name, $value);
    public function getParameter($name);
    public function hasParameter($name);
    public function removeParameter($name);
}
