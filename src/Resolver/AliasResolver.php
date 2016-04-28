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

use Interop\Container\ContainerInterface;
use Stack\DI\Alias;

/**
 * Resolves an alias definition to a value.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
class AliasResolver
{
    /**
     * @var array
     */
    private $aliases = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * AliasResolver constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Resolve an alias definition to a value.
     * This will return the entry the alias points to.
     *
     * @param string $name Entry name.
     *
     * @return mixed
     */
    public function resolve($name)
    {
        $name = $this->getAlias($name);

        return $this->container->get($name);
    }

    /**
     * Check if a definition can be resolved.
     *
     * @param string $name Entry name.
     *
     * @return bool
     */
    public function isResolvable($name)
    {
        if (!$this->hasAlias($name)) {
            return false;
        }

        $name = $this->getAlias($name);

        return $this->container->has($name);
    }

    /**
     * @param string $alias
     * @return mixed
     */
    public function getAlias($alias)
    {
        return $this->aliases[$alias];
    }

    /**
     * @param string $alias
     * @return bool
     */
    public function hasAlias($alias)
    {
        return isset($this->aliases[$alias]);
    }

    /**
     * @param Alias $alias
     */
    public function setAlias(Alias $alias)
    {
        $this->aliases[$alias->getName()] = $alias->getTargetName();
    }
}
