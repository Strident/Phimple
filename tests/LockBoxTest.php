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

    public function testSetItemWithObject()
    {
        $lockbox = new LockBox();
        $lockbox->set('item', new Fixtures\Service());

        $this->assertInstanceOf('Phimple\Tests\Fixtures\Service', $lockbox->get('item'));
    }

    /**
     * @expectedException Phimple\Exception\LockedItemException
     * @expectedExceptionMessage Cannot override locked item "item".
     */
    public function testSetValidatesItemIsNotLocked()
    {
        $lockbox = new LockBox();
        $lockbox->set('item', new Fixtures\Service());
        $lockbox->lock('item');
        $lockbox->set('item', new Fixtures\Service());
    }

    /**
     * @expectedException Phimple\Exception\ItemNotFoundException
     * @expectedExceptionMessage Item "item" could not be found.
     */
    public function testGetValidatesKeyIsPresent()
    {
        $lockbox = new LockBox();
        $lockbox->get('item');
    }

    public function testHasItem()
    {
        $lockbox = new LockBox();
        $lockbox->set('item', 'value');

        $this->assertTrue($lockbox->has('item'));
        $this->assertFalse($lockbox->has('not_here'));
    }

    public function testRemoveItem()
    {
        $lockbox = new LockBox();
        $lockbox->set('item', 'value');

        $this->assertTrue($lockbox->has('item'));

        $lockbox->remove('item');

        $this->assertFalse($lockbox->has('item'));
    }

    public function testLockAndUnlockItem()
    {
        $lockbox = new LockBox();
        $lockbox->set('item', 'invalid');
        $lockbox->lock('item');

        $this->assertTrue($lockbox->isLocked('item'));

        $lockbox->unlock('item');

        $this->assertFalse($lockbox->isLocked('item'));
    }

    /**
     * @expectedException Phimple\Exception\ItemNotFoundException
     * @expectedExceptionMessage Item "item" could not be found.
     */
    public function testLockValidatesKeyIsPresent()
    {
        $lockbox = new LockBox();
        $lockbox->lock('item');
    }

    /**
     * @expectedException Phimple\Exception\ItemNotFoundException
     * @expectedExceptionMessage Item "item" could not be found.
     */
    public function testUnlockValidatesKeyIsPresent()
    {
        $lockbox = new LockBox();
        $lockbox->unlock('item');
    }

    /**
     * @expectedException Phimple\Exception\ItemNotFoundException
     * @expectedExceptionMessage Item "item" could not be found.
     */
    public function testIsLockedAfterRemove()
    {
        $lockbox = new LockBox();
        $lockbox->set('item', 'value');
        $lockbox->lock('item');
        $lockbox->remove('item');
        $lockbox->isLocked('item');
    }

    public function testCountItems()
    {
        $lockbox = new LockBox();
        $lockbox->set('item1', 'value1');
        $lockbox->set('item2', 'value2');
        $lockbox->set('item3', 'value3');
        $lockbox->set('item4', 'value4');

        $this->assertEquals(4, $lockbox->count());

        $lockbox->remove('item4');

        $this->assertEquals(3, $lockbox->count());
    }

    public function testContents()
    {
        $lockbox = new LockBox();
        $lockbox->set('item1', 'value1');
        $lockbox->set('item2', 'value2');
        $lockbox->set('item3', 'value3');
        $lockbox->set('item4', 'value4');

        $this->assertEquals([
            'item1',
            'item2',
            'item3',
            'item4'
        ], $lockbox->contents());
    }
}
