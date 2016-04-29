<?php
/**
 * This file is part of the Stack package.
 *
 * (c) Andrzej Kostrzewa <andkos11@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stack\DI\Injection;

use Interop\Container\ContainerInterface;
use Stack\DI\Resolver\Resolver;

/**
 * A factory to create objects and values for injection into the Container.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
class InjectionFactory
{
    /**
     * A Resolver to provide class-creation specifics.
     *
     * @var Resolver
     */
    private $resolver;

    /**
     * InjectionFactory constructor.
     *
     * @param Resolver $resolver
     */
    public function __construct(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Returns a new LazyObject.
     *
     * @param callable $callable The callable to invoke.
     * @param array    $params   Arguments for the callable.
     *
     * @return LazyObject
     */
    public function newLazyObject($callable, array $params = [])
    {
        return new LazyObject($callable, $params);
    }

    /**
     * Returns a new LazyNewObject.
     *
     * @param string $class   The class to instantiate.
     * @param array  $params  Params for the instantiation.
     * @param array  $setters Setters for the instantiation.
     *
     * @return LazyNewObject
     */
    public function newLazyNewObject(
        $class,
        array $params = [],
        array $setters = []
    ) {
        return new LazyNewObject($this->resolver, $class, $params, $setters);
    }

    /**
     * Returns a new LazyGetObject.
     *
     * @param ContainerInterface $container The service container.
     * @param string             $service   The service to retrieve.
     *
     * @return LazyGetObject
     */
    public function newLazyGetObject(ContainerInterface $container, $service)
    {
        return new LazyGetObject($container, $service);
    }
}
