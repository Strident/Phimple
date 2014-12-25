<?php

/**
 * This file is part of the Phimple package.
 *
 * (c) Elliot Wright <elliot@elliotwright.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phimple\Exception;

/**
 * Locked Item Exception
 *
 * @author Elliot Wright <elliot@elliotwright.co>
 */
class LockedItemException extends \RuntimeException
{
    /**
     * Constructor.
     *
     * @param string     $name
     * @param \Exception $previous
     */
    public function __construct($name, \Exception $previous = null)
    {
        parent::__construct(sprintf('Cannot override locked item "%s".', $name), 0, $previous);
    }
}
