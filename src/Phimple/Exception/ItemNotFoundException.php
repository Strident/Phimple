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
 * Item Not Found Exception
 *
 * @author Elliot Wright <elliot@elliotwright.co>
 */
class ItemNotFoundException extends \InvalidArgumentException
{
    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct($name, \Exception $previous = null)
    {
        parent::__construct(sprintf('Item "%s" could not be found.', $name), 0, $previous);
    }
}
