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

use Phimple\Container;

/**
 * @author Elliot Wright <elliot@elliotwright.co>
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testSetParamWithString()
    {
        $container = new Container();
        $container->setParameter('param', 'value');

        $this->assertEquals('value', $container->getParameter('param'));
    }

    public function testSetServiceWithClosure()
    {
        $container = new Container();
        $container->set('service', function($c) {
            return new Fixtures\Service();
        });

        $this->assertInstanceOf('Phimple\Tests\Fixtures\Service', $container->get('service'));
    }

    public function testFactoryServicesShouldBeDifferent()
    {
        $container = new Container();
        $container->set('service', $container->factory(function($c) {
            return new Fixtures\Service();
        }));

        $serviceOne = $container->get('service');
        $serviceTwo = $container->get('service');

        $this->assertInstanceOf('Phimple\Tests\Fixtures\Service', $serviceOne);
        $this->assertInstanceOf('Phimple\Tests\Fixtures\Service', $serviceTwo);

        $this->assertNotSame($serviceOne, $serviceTwo);
    }

    public function testServiceShouldPassContainerAsParameter()
    {
        $container = new Container();
        $container->set('service', function($c) {
            return new Fixtures\Service();
        });

        $container->set('container', function($c) {
            return $c;
        });

        $this->assertNotSame($container, $container->get('service'));
        $this->assertSame($container, $container->get('container'));
    }

    public function testHasService()
    {
        $container = new Container();
        $container->setParameter('param', 'value');
        $container->setParameter('null', null);
        $container->set('service', function($c) {
            return new Fixtures\Service();
        });

        $this->assertTrue($container->hasParameter('param'));
        $this->assertTrue($container->hasParameter('null'));
        $this->assertTrue($container->has('service'));
        $this->assertFalse($container->hasParameter('not_here'));
        $this->assertFalse($container->has('not_here'));
    }

    /**
     * @dataProvider serviceDefinitionProvider
     */
    public function testServiceShouldBeShared($service)
    {
        $container = new Container();
        $container->set('shared', $service);

        $serviceOne = $container->get('shared');
        $serviceTwo = $container->get('shared');

        $this->assertInstanceOf('Phimple\Tests\Fixtures\Service', $serviceOne);
        $this->assertInstanceOf('Phimple\Tests\Fixtures\Service', $serviceTwo);

        $this->assertSame($serviceOne, $serviceTwo);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Service "foo" is not defined.
     */
    public function testGetValidtesKeyIsPresent()
    {
        $container = new Container();
        $container->get('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parameter "foo" is not defined.
     */
    public function testGetParamValidtesKeyIsPresent()
    {
        $container = new Container();
        $container->getParameter('foo');
    }

    /**
     * @dataProvider serviceDefinitionProvider
     */
    public function testExtend($service)
    {
        $container = new Container();
        $container->set('shared', function($c) {
            return new Fixtures\Service();
        });

        $container->set('factory', $container->factory(function($c) {
            return new Fixtures\Service();
        }));

        $container->extend('shared', $service);
        $serviceOne = $container->get('shared');
        $serviceTwo = $container->get('shared');

        $this->assertInstanceOf('Phimple\Tests\Fixtures\Service', $serviceOne);
        $this->assertInstanceOf('Phimple\Tests\Fixtures\Service', $serviceTwo);
        $this->assertSame($serviceOne, $serviceTwo);
        $this->assertSame($serviceOne->value, $serviceTwo->value);

        $container->extend('factory', $service);
        $serviceOne = $container->get('factory');
        $serviceTwo = $container->get('factory');

        $this->assertInstanceOf('Phimple\Tests\Fixtures\Service', $serviceOne);
        $this->assertInstanceOf('Phimple\Tests\Fixtures\Service', $serviceTwo);
        $this->assertNotSame($serviceOne, $serviceTwo);
        $this->assertNotSame($serviceOne->value, $serviceTwo->value);
    }

    public function testExtendDoesNotLeakWithFactories()
    {
        $container = new Container();
        $container->set('foo', $container->factory(function($c) { return; }));
        $container->set('foo', $container->extend('foo', function($foo, $c) { return; }));
        $container->remove('foo');

        $s = new \ReflectionProperty($container, 'services');
        $s->setAccessible(true);
        $i = new \ReflectionProperty($s->getValue($container), 'items');
        $i->setAccessible(true);
        $this->assertEmpty($i->getValue($s->getValue($container)));

        $f = new \ReflectionProperty($container, 'factories');
        $f->setAccessible(true);
        $this->assertCount(0, $f->getValue($container));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Service "foo" is not defined.
     */
    public function testExtendValidtesServiceIsPresent()
    {
        $container = new Container();
        $container->extend('foo', function($c) {});
    }

    public function testKeys()
    {
        $container = new Container();
        $container->set('foo', function($c) {});
        $container->set('bar', function($c) {});

        $this->assertEquals(['foo', 'bar'], $container->keys());
    }

    public function testSettingAnInvokableObjectShouldTreatItAsFactory()
    {
        $container = new Container();
        $container->set('invokable', new Fixtures\Invokable());

        $this->assertInstanceOf('Phimple\Tests\Fixtures\Service', $container->get('invokable'));
    }

    public function testSettingNonInvokableObjectShouldTreatItAsInvokedService()
    {
        $container = new Container();
        $container->set('non_invokable', new Fixtures\NonInvokable());

        $this->assertInstanceOf('Phimple\Tests\Fixtures\NonInvokable', $container->get('non_invokable'));
    }

    /**
     * @dataProvider badServiceDefinitionProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Service definition is not a Closure or invokable object.
     */
    public function testFactoryFailsForInvalidServiceDefinitions($service)
    {
        $container = new Container();
        $container->factory($service);
    }

    /**
     * @dataProvider badServiceDefinitionProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Service "foo" does not contain an object definition.
     */
    public function testExtendFailsForInvalidServiceDefinitions($service)
    {
        $container = new Container();
        $container->set('foo', $service);
        $container->extend('foo', function($c) {});
    }

    /**
     * @dataProvider badServiceDefinitionProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Extension service definition is not a Closure or invokable object.
     */
    public function testExtendFailsForEmptyServiceDefinitions($service)
    {
        $container = new Container();
        $container->set('foo', function($c) {});
        $container->extend('foo', $service);
    }

    /**
     * Provider for service definitions
     */
    public function badServiceDefinitionProvider()
    {
        return array(
            array(123),
            array(new Fixtures\NonInvokable())
        );
    }

    /**
     * Provider for service definitions
     */
    public function serviceDefinitionProvider()
    {
        return array(
            array(function($value) {
                $service = new Fixtures\Service();
                $service->value = $value;

                return $service;
            }),
            array(new Fixtures\Invokable())
        );
    }

    public function testDefiningNewServiceAfterLock()
    {
        $container = new Container();
        $container->set('foo', function() {
            return 'foo';
        });

        $foo = $container->get('foo');

        $container->set('bar', function() {
            return 'bar';
        });

        $this->assertSame('bar', $container->get('bar'));
    }

    /**
     * @expectedException Phimple\Exception\LockedItemException
     * @expectedExceptionMessage Cannot override locked item "foo".
     */
    public function testOverridingServiceAfterLock()
    {
        $container = new Container();
        $container->set('foo', function() {
            return 'foo';
        });

        $foo = $container->get('foo');

        $container->set('foo', function() {
            return 'bar';
        });
    }

    public function testRemovingServiceAfterLock()
    {
        $container = new Container();
        $container->set('foo', function() {
            return 'foo';
        });

        $foo = $container->get('foo');

        $container->remove('foo');
        $container->set('foo', function() {
            return 'bar';
        });

        $this->assertSame('bar', $container->get('foo'));
    }

    public function testExtendingService()
    {
        $container = new Container();
        $container->set('foo', function($c) {
            return 'foo';
        });

        $container->extend('foo', function($foo, $c) {
            return "$foo.bar";
        });

        $container->extend('foo', function($foo, $c) {
            return "$foo.baz";
        });

        $this->assertSame('foo.bar.baz', $container->get('foo'));
    }

    public function testExtendingServiceAfterOtherServiceLock()
    {
        $container = new Container();
        $container->set('foo', function($c) {
            return 'foo';
        });

        $container->set('bar', function($c) {
            return 'bar';
        });

        $foo = $container->get('foo');

        $container->extend('bar', function($bar, $c) {
            return "$bar.baz";
        });

        $this->assertSame('bar.baz', $container->get('bar'));
    }
}
