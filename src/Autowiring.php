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

use Stack\DI\Annotation\InjectableInterface;

/**
 * Reads DI class definitions using reflection.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
final class Autowiring implements InjectableInterface
{
    /**
     * @var array
     */
    private $definitions = [];

    /**
     * {@inheritdoc}
     */
    public function add(array $definitions)
    {
        $this->definitions = $definitions;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if ($this->has($name)) {
            return $this->definitions[$name];
        }

        if (!class_exists($name) && !interface_exists($name)) {
            return null;
        }

        $autowiring = function ($name) {
            $class = new \ReflectionClass($name);

            $constructor = $class->getConstructor();

            if ($constructor && $constructor->isPublic()) {
                $parametersDefinitions = $this->getParametersDefinition($constructor);
                if ($constructor->getNumberOfRequiredParameters() !== count($parametersDefinitions)) {
                    return null;
                }

                $object = $class->newInstanceArgs($parametersDefinitions);
                $this->set($name, $object);
                
                return $object;
            }

            $object = $class->newInstanceWithoutConstructor();
            $this->set($name, $object);

            return $object;
        };

        return $autowiring($name);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return isset($this->definitions[$name]) || array_key_exists($name, $this->definitions);
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        $this->definitions[$name] = $value;
    }

    /**
     * Get Class definition for constructor parameter.
     *
     * @param \ReflectionClass $parameterClass
     * @return mixed|null|object
     */
    private function getClassDefinition(\ReflectionClass $parameterClass)
    {

        $parameterClassName = $parameterClass->getName();
        $entryReference = new \ReflectionClass($parameterClass->getName());
        $argumentParams = false;

        if ($entryReference->getConstructor()) {
            $argumentParams = $entryReference->getConstructor()->getParameters();
        }

        return $argumentParams ? $this->get($parameterClassName) : new $parameterClassName();

    }

    /**
     * Get constructor parameters definitions.
     *
     * @param \ReflectionFunctionAbstract $constructor
     * @return array
     */
    public function getParametersDefinition(\ReflectionFunctionAbstract $constructor)
    {
        $parameters = [];

        foreach ($constructor->getParameters() as $index => $parameter) {
            if ($parameter->isOptional()) {
                continue;
            }

            $parameterClass = $parameter->getClass();
            if ($parameterClass) {
                $parameters[$index] = $this->getClassDefinition($parameterClass);
            }
        }

        return $parameters;
    }
}
