<?php
/**
 * This file is part of the Stack package.
 *
 * (c) Andrzej Kostrzewa <andkos11@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stack\DI;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    const HELLO = 'hello';
    const FOO = 'Stack\DI\Fixtures\Foo';

    public function testHas()
    {
        $container = new Container();
        $container->set('hi', ContainerTest::HELLO);
        $container->get(ContainerTest::FOO);

        $this->assertTrue($container->has(ContainerTest::FOO));
        $this->assertTrue($container->has('Foo'));
        $this->assertTrue($container->has('hi'));
        $this->assertFalse($container->has('Bar'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The name parameter must be of type string
     */
    public function testHasNonStringParameter()
    {
        $container = new Container();
        $container->has(new \stdClass());
    }

    public function testSet()
    {
        $container = new Container();
        $container->set(
            'Foo',
            function () {
                return ContainerTest::FOO;
            }
        );
        $container->get(ContainerTest::FOO);

        $this->assertTrue($container->has('Foo'));
    }

    public function testSetNullValue()
    {
        $container = new Container();
        $container->set('Foo', null);
        $container->set('Bar', function () {
            return null;
        });
        
        $this->assertNull($container->get('Foo'));
        $this->assertNull($container->get('Bar'));
    }

    public function testSetGetSetGet()
    {
        $container = new Container();
        $container->set('foo', 'bar');
        $container->get('foo');
        $container->set('foo', ContainerTest::HELLO);

        $this->assertSame(ContainerTest::HELLO, $container->get('foo'));
    }
}
