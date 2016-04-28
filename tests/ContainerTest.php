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

use Stack\DI\Fixtures\OtherClassFixture;
use Stack\DI\Fixtures\ParentClassFixture;
use Stack\DI\Injection\InjectionFactory;
use Stack\DI\Resolver\Reflector;
use Stack\DI\Resolver\Resolver;

/**
 * Class ContainerTest.
 *
 * @covers Stack\DI\Container
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    protected $container;

    public function setUp()
    {
        parent::setUp();
        $this->container = ContainerBuilder::buildDevContainer();
    }

    public function testHasGet()
    {
        $expect = (object) [];
        $this->container->set('foo', $expect);

        $this->assertTrue($this->container->has('foo'));
        $this->assertFalse($this->container->has('bar'));

        $actual = $this->container->get('foo');
        $this->assertSame($expect, $actual);

        $again = $this->container->get('foo');
        $this->assertSame($actual, $again);
    }

    public function testGetNoSuchService()
    {
        $this->setExpectedException('Stack\DI\Exception\ServiceNotFound');
        $this->container->get('foo');
    }

    public function testGetServiceInsideClosure()
    {
        $di = $this->container;
        $di->set('foo', function () use ($di) {
            return new ParentClassFixture();
        });

        $actual = $this->container->get('foo');
        $this->assertInstanceOf('Stack\DI\Fixtures\ParentClassFixture', $actual);
    }

    public function testLazyGet()
    {
        $this->container->set('foo', function () {
            return new OtherClassFixture();
        });
        
        $lazy = $this->container->lazyGet('foo');
        $this->assertInstanceOf('Stack\DI\Injection\LazyGetObject', $lazy);
        
        $foo = $lazy();
        $this->assertInstanceOf('Stack\DI\Fixtures\OtherClassFixture', $foo);
    }

    public function testGetWithDefaults()
    {
        $instance = $this->container->get('Stack\DI\Fixtures\ParentClassFixture');

        $expect = 'bar';
        $actual = $instance->getFoo();
        $this->assertSame($expect, $actual);
    }

    public function testMake()
    {
        $instance = $this->container->make(
            'Stack\DI\Fixtures\ParentClassFixture',
            ['foo' => 'other']
        );

        $expect = 'other';
        $actual = $instance->getFoo();
        $this->assertSame($expect, $actual);
    }

    public function testMakeWithSetter()
    {
        $instance = $this->container->make(
            'Stack\DI\Fixtures\ChildClassFixture',
            [
                'foo' => 'other',
                'other' => new OtherClassFixture(),
            ],
            [
                'setFake' => 'fake'
            ]
        );

        $expect = 'fake';
        $actual = $instance->getFake();
        $this->assertSame($expect, $actual);
    }

    public function testMakeWithLazySetter()
    {
        $di = $this->container;
        $actual = $this->container->make(
            'Stack\DI\Fixtures\ChildClassFixture',
            [
                'foo' => 'bar',
                new OtherClassFixture(),
            ],
            [
                'setFake' => $di->lazyGet('Stack\DI\Fixtures\OtherClassFixture')
            ]
        );

        $this->assertInstanceOf('Stack\DI\Fixtures\OtherClassFixture', $actual->getFake());
    }

    public function testMakeWithNonExistentSetter()
    {
        $this->setExpectedException('Stack\DI\Exception\SetterMethodNotFound');
        $this->container->make(
            'Stack\DI\Fixtures\OtherClassFixture',
            [],
            ['setFakeNotExists' => 'fake']
        );
    }

    public function testMakeWithPositionalParams()
    {
        $other = $this->container->get('Stack\DI\Fixtures\OtherClassFixture');
        $actual = $this->container->make('Stack\DI\Fixtures\ChildClassFixture', [
            'foofoo',
            $other,
        ]);

        $this->assertInstanceOf('Stack\DI\Fixtures\ChildClassFixture', $actual);
        $this->assertInstanceOf('Stack\DI\Fixtures\OtherClassFixture', $actual->getOther());
        $this->assertSame('foofoo', $actual->getFoo());

        $actual = $this->container->make('Stack\DI\Fixtures\ChildClassFixture', [
            0 => 'keepme',
            'foo' => 'bad',
            $other,
        ]);

        $this->assertInstanceOf('Stack\DI\Fixtures\ChildClassFixture', $actual);
        $this->assertInstanceOf('Stack\DI\Fixtures\OtherClassFixture', $actual->getOther());
        $this->assertSame('keepme', $actual->getFoo());
    }

    public function testCall()
    {
        $lazy = $this->container->call('Stack\DI\Fixtures\ParentClassFixture', 'mirror', 'foo');
        $this->assertInstanceOf('Stack\DI\Injection\LazyObject', $lazy);

        $actual = $lazy();
        $expect = 'foo';
        $this->assertSame($expect, $actual);
        $di = $this->container;
        $lazy = $this->container->call(
            'Stack\DI\Fixtures\ParentClassFixture',
            'mirror',
            $di->lazyGet('Stack\DI\Fixtures\OtherClassFixture')
        );
        $this->assertInstanceOf('Stack\DI\Injection\LazyObject', $lazy);

        $actual = $lazy();
        $this->assertInstanceOf('Stack\DI\Fixtures\OtherClassFixture', $actual);
    }

    public function testResolveWithMissingParam()
    {
        $this->setExpectedException(
            'Stack\DI\Exception\MissingParam',
            'Stack\DI\Fixtures\ResolveClassFixture::$fake'
        );

        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $container = $builder->build();
        $container->get('Stack\DI\Fixtures\ResolveClassFixture');
    }

    public function testResolveWithMissingParams()
    {
        $this->setExpectedException(
            'Stack\DI\Exception\MissingParam',
            'Stack\DI\Fixtures\ResolveClassFixture1::$foo'
        );
        $di = $this->container;
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'Stack\DI\Fixtures\OtherClassFixture' => $di->lazyGet('Stack\DI\Fixtures\OtherClassFixture')
        ]);

        $container = $builder->build();
        $container->make(
            'Stack\DI\Fixtures\ResolveClassFixture1',
            ['fake' => $di->lazyGet('Stack\DI\Fixtures\ParentClassFixture')]
        );
    }

    public function testResolveWithoutMissingParam()
    {
        $di = $this->container;
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'fake' => $di->lazyGet('Stack\DI\Fixtures\ParentClassFixture')
        ]);
        $builder->useAutowiring(false);
        $container = $builder->build();

        $actual = $container->get('Stack\DI\Fixtures\ResolveClassFixture');

        $this->assertInstanceOf('Stack\DI\Fixtures\ResolveClassFixture', $actual);
    }

    public function testUseAnnotation()
    {
        $di = $this->container;
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'fake' => $di->lazyGet('Stack\DI\Fixtures\ParentClassFixture')
        ]);
        $builder->useAnnotation(true);
        $container = $builder->build();

        $actual = $container->get('Stack\DI\Fixtures\ResolveClassFixture');

        $this->assertInstanceOf('Stack\DI\Fixtures\ResolveClassFixture', $actual);
    }

    public function testDependencyLookupSimple()
    {
        $delegateContainer = ContainerBuilder::buildDevContainer();
        $delegateContainer->set('foo', function () {
            $obj = new \stdClass();
            $obj->foo = "bar";
            return $obj;
        });
       
        $container = new Container(new InjectionFactory(new Resolver(new Reflector())), $delegateContainer);
        $lazy = $container->lazyGet('foo');
        $this->assertInstanceOf('Stack\DI\Injection\LazyGetObject', $lazy);
        $foo = $lazy();
        $this->assertInstanceOf('stdClass', $foo);
        $this->assertEquals('bar', $foo->foo);
        $actual = $container->getDelegateContainer();
        $this->assertSame($delegateContainer, $actual);

        $builder = new ContainerBuilder();
        $builder->setDelegateContainer($delegateContainer);

        $container = $builder->build();
        $actual = $container->getDelegateContainer();
        $this->assertSame($delegateContainer, $actual);
    }

    public function testHonorsInterfacesAndOverrides()
    {
        $resolver = new Resolver(new Reflector());
        $resolver->addSetters(['Stack\DI\Fixtures\InterfaceFixture' => ['setFoo' => 'initial']]);
        $resolver->addSetters(['Stack\DI\Fixtures\InterfaceClass2Fixture' => ['setFoo' => 'override']]);
        $container = new Container(new InjectionFactory($resolver));
        $actual = $container->get('Stack\DI\Fixtures\InterfaceClassFixture');
        $this->assertSame('initial', $actual->getFoo());

        $actual = $container->get('Stack\DI\Fixtures\InterfaceClass1Fixture');
        $this->assertSame('initial', $actual->getFoo());

        $actual = $container->get('Stack\DI\Fixtures\InterfaceClass2Fixture');
        $this->assertSame('override', $actual->getFoo());

        $actual = $container->get('Stack\DI\Fixtures\InterfaceClass3Fixture');
        $this->assertSame('override', $actual->getFoo());
    }
}
