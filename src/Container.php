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


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Interop\Container\Exception\NotFoundException;
use Stack\DI\Definition\AliasDefinition;
use Stack\DI\Definition\Source\DefinitionSourceInterface;
use Stack\DI\Exception\ServiceNotFoundException;

/**
 * Dependency Injection Container.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
class Container implements ContainerInterface
{
    /**
     * @var callable[]
     */
    private $serviceFactory = [];

    /**
     * @var array
     */
    private $services = [];

    /**
     * @var ContainerInterface
     */
    private $delegateContainer;

    /**
     * @var DefinitionSourceInterface
     */
    private $definitionSource;

    /**
     * @var array
     */
    private $aliasDefinitions = [];

    /**
     * @var bool
     */
    private $useServiceFactory = false;

    /**
     * Container constructor.
     *
     * @param DefinitionSourceInterface $definitionSource
     * @param ContainerInterface $delegateContainer
     */
    public function __construct(DefinitionSourceInterface $definitionSource, ContainerInterface $delegateContainer = null)
    {
        $this->definitionSource = $definitionSource;
        $this->delegateContainer = $delegateContainer;
    }


    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        $service = $id;
        $id = strtolower($id);

        if (!$this->hasAlias($id) && !$this->has($id)) {
            $this->setAlias($id);
        }

        if ($this->hasAlias($id)) {
            $id = $this->getAlias($id);
        }

        if ($this->has($id)) {
            $this->useServiceFactory = false;

            return $this->services[$id];
        }

        if ($this->useServiceFactory) {
            $service = $this->getServiceFromFactory($id);

            if ($this->definitionSource === null) {
                $this->services[$id] = $service;

                return $service;
            }
        }

        if ($this->definitionSource !== null) {
            $service = $this->definitionSource->get($service);
            $this->services[$id] = $service;

            return $service;
        }

        throw new ServiceNotFoundException('Service not found: '.$id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($id)
    {
        if (!is_string($id)) {
            throw new \InvalidArgumentException(sprintf(
                'The name parameter must be of type string, %s given',
                is_object($id) ? get_class($id) : gettype($id)
            ));
        }

        $serviceName = strtolower($id);

        if ($this->hasAlias($serviceName)) {
            $serviceName = $this->aliasDefinitions[$serviceName];
        }

        return isset($this->services[$serviceName]) || array_key_exists($serviceName, $this->services);
    }

    /**
     * Define an object in the container.
     *
     * @param string $id Entry name
     * @param mixed $value Value
     */
    public function set($id, $value)
    {
        $id = strtolower($id);
        if ($value instanceof \Closure) {
            $this->useServiceFactory = true;
            $this->serviceFactory[$id] = $value;
            unset($this->services[$id]);
        } else {
            $this->services[$id] = $value;
        }
    }

    /**
     * @param string $id
     * 
     * @return mixed
     */
    private function getAlias($id)
    {
        if (!$this->hasAlias($id)) {
            throw new \InvalidArgumentException(sprintf('The service alias "%s" does not exist.', $id));
        }

        return $this->aliasDefinitions[$id];
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    private function hasAlias($id)
    {
        return isset($this->aliasDefinitions[$id]);
    }

    /**
     * @param string $id
     */
    private function setAlias($id)
    {
        $alias = new AliasDefinition();
        $alias->aliasFromNamespace($id);

        $this->aliasDefinitions[$alias->getTargetName()] = $alias->getName();
    }

    /**
     * Get service from Closure object.
     *
     * @param string $id
     *
     * @return mixed
     */
    private function getServiceFromFactory($id)
    {
        $serviceFactory = $this->serviceFactory[$id];
        $containerToUseForDependencies = $this->delegateContainer ?: $this;

        return $serviceFactory($containerToUseForDependencies);
    }
}