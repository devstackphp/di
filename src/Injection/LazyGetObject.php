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

/**
 * Returns a Container service when invoked.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
class LazyGetObject implements LazyInterface
{
    /**
     * The service container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * The service name to retrieve.
     *
     * @var string
     */
    private $service;

    /**
     * LazyGetObject constructor.
     *
     * @param ContainerInterface $container
     * @param string             $service
     */
    public function __construct(ContainerInterface $container, $service)
    {
        $this->container = $container;
        $this->service   = $service;
    }

    /**
     * Invokes the closure to create the instance.
     *
     * @return object The object created by the closure.
     */
    public function __invoke()
    {
        return $this->container->get($this->service);
    }
}
