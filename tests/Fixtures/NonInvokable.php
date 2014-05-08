<?php

/**
 * This file is part of the Phimple package.
 *
 * (c) Elliot Wright <elliot@elliotwright.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phimple\Tests\Fixtures;

/**
 * @author Elliot Wright <elliot@elliotwright.co>
 */
class NonInvokable
{
    public function __call($a, $b)
    {
    }
}
