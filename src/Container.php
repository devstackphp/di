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
use Stack\DI\Injection\InjectionFactory;
use Stack\DI\Injection\LazyInterface;
use Stack\DI\Resolver\AliasResolver;

/**
 * Dependency Injection Container.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
class Container implements ContainerInterface
{
    /**
     * @var AliasResolver
     */
    private $aliasResolver;

    /**
     * @var ContainerInterface|null
     */
    private $delegateContainer;

    /**
     * @var InjectionFactory
     */
    private $injectionFactory;

    /**
     * @var array
     */
    private $instances = [];

    /**
     * @var array
     */
    private $services = [];

    /**
     * Container constructor.
     *
     * @param InjectionFactory        $injectionFactory
     * @param ContainerInterface|null $delegateContainer
     */
    public function __construct(
        InjectionFactory $injectionFactory,
        ContainerInterface $delegateContainer = null
    ) {
        $this->aliasResolver          = new AliasResolver($this);
        $this->injectionFactory       = $injectionFactory;
        $this->delegateContainer      = $delegateContainer;
        $this->instances['Container'] = $this;
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
        if (isset($this->instances[$name]) || array_key_exists($name, $this->instances)) {
            return $this->instances[$name];
        }

        if ($this->aliasResolver->isResolvable($name)) {
            return $this->aliasResolver->resolve($name);
        }

        $this->aliasResolver->setAlias(new Alias($name));

        $this->instances[$name] = $this->getServiceInstance($name);

        return $this->instances[$name];
    }

    /**
     * Build an entry of the container by its name.
     * This method makes the container behave like a factory.
     *
     * @param string $name       Entry name or a class name.
     * @param array  $parameters Optional parameters to use to build the entry. Use this to force specific parameters
     *                           to specific values. Parameters not defined in this array will be resolved using
     *                           the container.
     * @param array  $setters    Optional setters to use to build the entry.
     *
     * @return mixed
     */
    public function make($name, array $parameters = [], array $setters = [])
    {
        $instance = $this->injectionFactory->newLazyNewObject($name, $parameters, $setters);

        return $instance();
    }

    /**
     * Returns a lazy object that gets a service.
     *
     * @param string $name The entry name; it does not need to exist yet.
     *
     * @return Injection\LazyGetObject
     */
    public function lazyGet($name)
    {
        return $this->injectionFactory->newLazyGetObject($this, $name);
    }

    /**
     * Call the given function using the given parameters.
     * Missing parameters will be resolved from the container.
     *
     * @param string $name   Entry name.
     * @param string $method The method to call on the service object.
     *
     * @var mixed $parameters,... Parameters to use in the method call.
     *
     * @return Injection\LazyObject
     */
    public function call($name, $method)
    {
        $callable = [$this->lazyGet($name), $method];

        $parameters = func_get_args();
        array_shift($parameters);
        array_shift($parameters);

        return $this->injectionFactory->newLazyObject($callable, $parameters);
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
        if (isset($this->services[$name])) {
            return true;
        }

        return isset($this->delegateContainer)
        && $this->delegateContainer->has($name);
    }

    /**
     * Define an object in the container.
     *
     * @param string $name  Entry name.
     * @param mixed  $value Value definition.
     *
     * @return $this
     */
    public function set($name, $value)
    {
        $isLazy = false;
        if ($value instanceof \Closure) {
            $value       = $this->injectionFactory->newLazyObject($value);
            $nameOfValue = $name;
            if (!is_string($value)) {
                $nameOfValue = new \ReflectionObject($value());
                $nameOfValue = $nameOfValue->getName();
            }
            $this->aliasResolver->setAlias(new Alias($nameOfValue, $name));
            $isLazy = true;
        }

        if (!$isLazy) {
            $this->instances[$name] = $value;
        }

        $this->services[$name] = $value;

        return $this;
    }

    /**
     * Returns the secondary delegate container.
     *
     * @return ContainerInterface|null
     */
    public function getDelegateContainer()
    {
        return $this->delegateContainer;
    }

    /**
     * Instantiates a service object by key, lazy-loading it as needed.
     *
     * @param string $name Entry name to get.
     *
     * @throws Exception\ServiceNotFound when the requested service does not exist.
     *
     * @return object
     */
    private function getServiceInstance($name)
    {
        if (!$this->has($name)) {
            $value                 = $this->injectionFactory->newLazyNewObject($name);
            $this->services[$name] = $value;
        }

        if (!isset($this->services[$name])) {
            return $this->delegateContainer->get($name);
        }

        $instance = $this->services[$name];

        if ($instance instanceof LazyInterface) {
            $instance = $instance();
        }

        return $instance;
    }
}
