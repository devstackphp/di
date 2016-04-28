<?php
/*
 * This file is part of the Stack package.
 *
 * (c) Andrzej Kostrzewa <andkos11@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stack\DI;

use stdClass;

/**
 * Test class for Container.
 *
 * @covers Stack\DI\Container
 */
class ContainerGetTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGet()
    {
        $container = ContainerBuilder::buildDevContainer();
        $dummy     = new stdClass();
        $container->set('key', $dummy);
        $this->assertSame($dummy, $container->get('key'));
    }

    public function testGetWithClassName()
    {
        $container = ContainerBuilder::buildDevContainer();
        $this->assertInstanceOf('stdClass', $container->get('stdClass'));
    }

    /**
     * Tests a class can be initialized with a parameter passed by reference.
     */
    public function testPassByReferenceParameter()
    {
        if (version_compare(phpversion(), '7.0.0', '<')) {
            $container = ContainerBuilder::buildDevContainer();
            $object    = $container->get('Stack\DI\Fixtures\PassByReferenceDependency');
            $this->assertInstanceOf('Stack\DI\Fixtures\PassByReferenceDependency', $object);
        }
    }
}
