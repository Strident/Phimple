<?php

/**
 * This file is part of the Phimple package.
 *
 * (c) Elliot Wright <elliot@elliotwright.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phimple\Tests;

use Phimple\LockBox;

/**
 * @author Elliot Wright <elliot@elliotwright.co>
 */
class LockBoxTest extends \PHPUnit_Framework_TestCase
{
    public function testSetItemWithString()
    {
        $lockbox = new LockBox();
        $lockbox->set('item', 'value');

        $this->assertEquals('value', $lockbox->get('item'));
    }
}
