<?php
/**
 * This file is part of the Stack package.
 *
 * (c) Andrzej Kostrzewa <andkos11@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stack\DI\Resolver;

use Stack\DI\Injection\LazyNewObject;

/**
 * Class AutoResolverTest.
 */
class AutoResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AutoResolver
     */
    protected $resolver;

    public function setUp()
    {
        parent::setUp();
        $this->resolver = new AutoResolver(new Reflector());
    }

    public function testMissingParam()
    {
        $actual = $this->resolver->resolve('Stack\DI\Fixtures\ResolveClassFixture');
        $this->assertInstanceOf('Stack\DI\Fixtures\ParentClassFixture', $actual->params['fake']);
    }

    public function testAutoResolveExplicit()
    {
        $this->resolver->add([
            'Stack\DI\Fixtures\ParentClassFixture' => new LazyNewObject(
                $this->resolver,
                'Stack\DI\Fixtures\ChildClassFixture'
            ),
        ]);
        $actual = $this->resolver->resolve('Stack\DI\Fixtures\ResolveClassFixture');
        $this->assertInstanceOf('Stack\DI\Fixtures\ChildClassFixture', $actual->params['fake']);
    }

    public function testAutoResolveMissingParam()
    {
        $this->setExpectedException('Stack\DI\Exception\MissingParam');
        $this->resolver->resolve('Stack\DI\Fixtures\ParamsClassFixture');
    }
}
