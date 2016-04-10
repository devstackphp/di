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
use Stack\DI\Annotation\Annotation;
use Stack\DI\Annotation\InjectableInterface;
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
     * @var InjectableInterface
     */
    private $injectable;

    /**
     * @var bool
     */
    private $useAutowiring = true;
    /**
     * @var bool
     */
    private $useAnnotation = false;

    /**
     * @var bool
     */
    private $useServiceFactory = false;

    /**
     * Container constructor.
     */
    public function __construct()
    {
        $this->injectable = new Autowiring();
    }

    /**
     * Add definitions to the container.
     *
     * @param array $definition
     * @return bool
     */
    public function addDefinitions(array $definition)
    {
        if ($this->injectable !== null) {
            $this->injectable->add($definition);

            return true;
        }

        return false;
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
        $serviceName = end(explode('\\', $service));
        if ($this->has($serviceName)) {
            $this->useServiceFactory = false;

            return $this->services[$serviceName];
        }

        if ($this->useServiceFactory) {
            $service = $this->getServiceFromFactory($serviceName);

            if (!$this->useAnnotation && !$this->useAutowiring) {
                $this->services[$serviceName] = $service;

                return $this->services[$serviceName];
            }
        }

        if ($this->injectable !== null) {
            $this->services[$serviceName] = $this->injectable->get($service);

            return $this->services[$serviceName];
        }

        throw new ServiceNotFoundException('Service not found: '.$id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws \InvalidArgumentException The name parameter must be of type string.
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

        $serviceName = end(explode('\\', $id));

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
        if ($value instanceof \Closure) {
            $this->useServiceFactory = true;
            $this->serviceFactory[$id] = $value;
            unset($this->services[$id]);
        } else {
            $this->services[$id] = $value;
        }
    }

    /**
     * Delegate the container for dependencies.
     *
     * @param ContainerInterface $delegateContainer
     */
    public function setDelegateContainer(ContainerInterface $delegateContainer)
    {
        $this->delegateContainer = $delegateContainer;
    }

    /**
     * Enable or disable the use of autowiring to guess injections.
     *
     * Enabled by default.
     *
     * @param $bool
     * @return $this
     */
    public function useAutowiring($bool)
    {
        $this->useAutowiring = $bool;
        $this->injectable = !$bool ? $this->injectable : new Autowiring();

        return $this;
    }

    /**
     * Enable or disable the use of annotations to guess injections.
     *
     * Disabled by default.
     *
     * @param $bool
     * @return $this
     */
    public function useAnnotation($bool)
    {
        $this->useAnnotation = $bool;
        $this->injectable = $bool && $this->useAutowiring ? new Annotation() : null;

        return $this;
    }

    /**
     * Get service from Closure object.
     *
     * @param $serviceId
     * @return mixed
     */
    private function getServiceFromFactory($serviceId)
    {
        $serviceFactory = $this->serviceFactory[$serviceId];
        $containerToUseForDependencies = $this->delegateContainer ?: $this;

        return $serviceFactory($containerToUseForDependencies);
    }
}
