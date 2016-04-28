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

use EasyMock\EasyMock;
use Interop\Container\ContainerInterface;
use Stack\DI\Fixtures\FakeContainer;

class ContainerBuilderTest extends \PHPUnit_Framework_TestCase
{
    use EasyMock;

    public function testNotDelegateContainerDefault()
    {
        $builder = new ContainerBuilder('Stack\DI\Fixtures\FakeContainer');

        /** @var FakeContainer $container */
        $container = $builder->build();

        $this->assertNull($container->delegateContainer);
    }

    public function testSetDelegateContainer()
    {
        /** @var ContainerInterface $otherContainer */
        $otherContainer = $this->easyMock('Interop\Container\ContainerInterface');
        $builder        = new ContainerBuilder('Stack\DI\Fixtures\FakeContainer');
        $builder->setDelegateContainer($otherContainer);

        /** @var FakeContainer $container */
        $container = $builder->build();

        $this->assertSame($otherContainer, $container->delegateContainer);
    }

    public function testAddCustomDefinitionSources()
    {
        $builder = new ContainerBuilder('Stack\DI\Fixtures\FakeContainer');
        $builder->addDefinitions(['foo' => 'bar']);
        $builder->addDefinitions(['foofoo' => 'barbar']);

        /** @var FakeContainer $container */
        $container = $builder->build();

        $definition = $container->definitionSource->get('foo');
        $this->assertSame('bar', $definition);
        $definition = $container->definitionSource->get('foofoo');
        $this->assertSame('barbar', $definition);
    }

    public function testReverseOrderDefinition()
    {
        $builder = new ContainerBuilder('Stack\DI\Fixtures\FakeContainer');
        $builder->addDefinitions(['foo' => 'bar']);
        $builder->addDefinitions(['foo' => 'bim']);

        /** @var FakeContainer $container */
        $container = $builder->build();

        $definition = $container->definitionSource->get('foo');
        $this->assertSame('bim', $definition);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ContainerBuilder::addDefinitions() parameter must be an array, string given
     */
    public function testAddingInvalidDefinitions()
    {
        $builder = new ContainerBuilder('Stack\DI\Fixtures\FakeContainer');
        $builder->addDefinitions('123');
    }

    public function testFluentInterface()
    {
        $builder = new ContainerBuilder();
        $result  = $builder->useAnnotation(false);
        $this->assertSame($builder, $result);
        $result = $builder->useAnnotation(true);
        $this->assertSame($builder, $result);
        $result = $builder->useAutowiring(false);
        $this->assertSame($builder, $result);
        $result = $builder->useAutowiring(true);
        $this->assertSame($builder, $result);
        /** @var ContainerInterface $otherContainer */
        $otherContainer = $this->easyMock('Interop\Container\ContainerInterface');
        $result         = $builder->setDelegateContainer($otherContainer);
        $this->assertSame($builder, $result);
    }
}
