<?php
/**
 * This file is part of the Stack package.
 *
 * (c) Andrzej Kostrzewa <andkos11@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Stack\DI\Definition;

/**
 * Defines how an object can be instantiated.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
class ObjectDefinition extends \stdClass
{
    /**
     * Entry name (most of the time, same as $className).
     *
     * @var string
     */
    private $name;

    /**
     * Class name (if null, then the class name is $name).
     *
     * @var string|null
     */
    private $className;

    /**
     * @var array
     */
    private $constructorInjection = [];

    /**
     * @var array
     */
    private $methodInjections = [];

    /**
     * ObjectDefinition constructor.
     *
     * @param string $name      Entry name
     * @param null   $className Class name
     */
    public function __construct($name, $className = null)
    {
        $this->name      = $name;
        $this->className = $className;
    }

    /**
     * @return string Class name
     */
    public function getClassName()
    {
        if ($this->className !== null) {
            return $this->className;
        }

        return $this->name;
    }

    /**
     * @return string Entry name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add method parameters.
     *
     * @param $name
     * @param array $methodInjection
     */
    public function addMethodInjection($name, array $methodInjection)
    {
        if (!isset($this->methodInjections[$name])) {
            $this->methodInjections[$name] = [];
        }

        $this->methodInjections[$name][] = $methodInjection;
    }

    /**
     * Set property definition.
     *
     * @param \ReflectionProperty $property
     * @param $target
     */
    public function setPropertyInjection(\ReflectionProperty $property, $target)
    {
        if (!$property->isPublic()) {
            $property->setAccessible(true);
        }
        $property->setValue($this, $target);
    }

    /**
     * Set constructor parameters.
     *
     * @param array $methodInjection
     */
    public function setConstructorInjection(array $methodInjection)
    {
        $this->constructorInjection = $methodInjection;
    }

    /**
     * Get method parameters.
     *
     * @param $name
     *
     * @return array
     */
    public function getMethodParameters($name)
    {
        if (isset($this->methodInjections[$name])) {
            return $this->methodInjections[$name][0];
        }

        return [];
    }

    /**
     * New Instance of defined class.
     *
     * @param bool $withConstructor
     *
     * @return object|\ReflectionClass
     */
    public function getNewInstance($withConstructor = true)
    {
        $object = new \ReflectionClass($this->name);

        return $withConstructor ?
            $object->newInstanceArgs($this->constructorInjection)
            : $object->newInstanceWithoutConstructor();
    }
}
