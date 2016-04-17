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
     * @param ContainerInterface        $delegateContainer
     */
    public function __construct(DefinitionSourceInterface $definitionSource, ContainerInterface $delegateContainer = null)
    {
        $this->definitionSource = $definitionSource;
        $this->delegateContainer = $delegateContainer;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $name Identifier of the entry to look for.
     *
     * @throws NotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($name)
    {
        $service = $name;
        $name = strtolower($name);

        if (!$this->hasAlias($name) && !$this->has($name)) {
            $this->setAlias($name);
        }

        if ($this->hasAlias($name)) {
            $name = $this->getAlias($name);
        }

        if ($this->has($name)) {
            $this->useServiceFactory = false;

            return $this->services[$name];
        }

        if ($this->useServiceFactory) {
            $service = $this->getServiceFromFactory($name);

            if ($this->definitionSource === null) {
                $this->services[$name] = $service;

                return $service;
            }
        }

        if ($this->definitionSource !== null) {
            $service = $this->definitionSource->get($service);
            $this->services[$name] = $service;

            return $service;
        }

        throw new ServiceNotFoundException('Service not found: '.$name);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $name Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException(sprintf(
                'The name parameter must be of type string, %s given',
                is_object($name) ? get_class($name) : gettype($name)
            ));
        }

        $serviceName = strtolower($name);

        if ($this->hasAlias($serviceName)) {
            $serviceName = $this->aliasDefinitions[$serviceName];
        }

        return isset($this->services[$serviceName]) || array_key_exists($serviceName, $this->services);
    }

    /**
     * Define an object in the container.
     *
     * @param string $name  Entry name
     * @param mixed  $value Value
     */
    public function set($name, $value)
    {
        $name = strtolower($name);
        $isClosure = false;
        
        if ($value instanceof \Closure) {
            $this->useServiceFactory = true;
            $isClosure = true;
            $this->serviceFactory[$name] = $value;
            unset($this->services[$name]);
        } 
        
        if(!$isClosure) {
            $this->services[$name] = $value;
        }
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    private function getAlias($name)
    {
        if (!$this->hasAlias($name)) {
            throw new \InvalidArgumentException(sprintf('The service alias "%s" does not exist.', $name));
        }

        return $this->aliasDefinitions[$name];
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    private function hasAlias($name)
    {
        return isset($this->aliasDefinitions[$name]);
    }

    /**
     * @param string $name
     */
    private function setAlias($name)
    {
        $alias = new AliasDefinition();
        $alias->aliasFromNamespace($name);

        $this->aliasDefinitions[$alias->getTargetName()] = $alias->getName();
    }

    /**
     * Get service from Closure object.
     *
     * @param string $name
     *
     * @return mixed
     */
    private function getServiceFromFactory($name)
    {
        $serviceFactory = $this->serviceFactory[$name];
        $delegateContainer = $this->delegateContainer ?: $this;

        return $serviceFactory($delegateContainer);
    }
}
