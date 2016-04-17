<?php
/**
 * This file is part of the Stack package.
 *
 * (c) Andrzej Kostrzewa <andkos11@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Stack\DI\Definition\Source;

use Stack\DI\Definition\ObjectDefinition;

/**
 * Reads DI class definitions using reflection.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
class Annotation extends DefinitionSource
{
    /**
     * @var PhpDocReader
     */
    private $phpDocReader;

    /**
     * Returns the DI definition for the entry name.
     *
     * @param $name
     *
     * @return mixed|null|object
     */
    public function get($name)
    {
        if ($this->has($name)) {
            return $this->definitions[$name];
        }

        if (!class_exists($name) && !interface_exists($name)) {
            return;
        }

        $class = new \ReflectionClass($name);
        $object = new ObjectDefinition($name);

        $this->readProperties($class, $object);

        $object = $this->setMethods($class, $object);

        $this->set($name, $object);

        return $object;
    }

    /**
     * Get instance of PhpDocReader.
     *
     * @return PhpDocReader
     */
    private function getPhpDocReader()
    {
        if ($this->phpDocReader === null) {
            $this->phpDocReader = new PhpDocReader();
        }

        return $this->phpDocReader;
    }

    /**
     * Browse the class properties looking for annotated properties.
     *
     * @param \ReflectionClass $class
     * @param ObjectDefinition $object
     */
    private function readProperties(\ReflectionClass $class, ObjectDefinition $object)
    {
        $namespace = $class->getNamespaceName();
        foreach ($class->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $this->setProperty($property, $object, $namespace);
        }

        while ($class = $class->getParentClass()) {
            foreach ($class->getProperties(\ReflectionProperty::IS_PRIVATE) as $property) {
                if ($property->isStatic()) {
                    continue;
                }

                $this->setProperty($property, $object, $namespace);
            }
        }
    }

    /**
     * Set Class property instance.
     *
     * @param \ReflectionProperty $property
     * @param ObjectDefinition    $objectDefinition
     * @param $namespace
     *
     * @throws \Stack\DI\Exception\AnnotationException
     */
    private function setProperty(\ReflectionProperty $property, ObjectDefinition $objectDefinition, $namespace)
    {
        $this->getPhpDocReader()->setNamespace($namespace);
        $propertyClass = $this->getPhpDocReader()->getPropertyClass($property);
        $propertyClassObject = $propertyClass ? $propertyClass->getNewInstance() : null;
        $objectDefinition->setPropertyInjection($property, $propertyClassObject);
        if ($propertyClass !== null) {
            $this->set($propertyClass->getClassName(), $propertyClassObject);
        }
    }

    /**
     * Browse the object's methods looking for annotated methods.
     *
     * @param \ReflectionClass $class
     * @param ObjectDefinition $objectDefinition
     *
     * @return object|\ReflectionClass
     */
    private function setMethods(\ReflectionClass $class, ObjectDefinition $objectDefinition)
    {
        $isConstructor = false;
        $methodName = [];
        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isStatic()) {
                continue;
            }

            $methodParameters = $this->getMethodParameters($method);
            if ($methodParameters === null) {
                continue;
            }

            if ($method->isConstructor()) {
                $objectDefinition->setConstructorInjection($methodParameters);
                $isConstructor = true;
            } else {
                $objectDefinition->addMethodInjection($method->getName(), $methodParameters);
                $methodName[] = $method->getName();
            }
        }

        $object = $objectDefinition->getNewInstance($isConstructor);
        foreach ($methodName as $name) {
            $methodReflection = new \ReflectionMethod($object, $name);
            $args = $objectDefinition->getMethodParameters($name);
            $methodReflection->invokeArgs($object, $args);
        }

        return $object;
    }

    /**
     * Get parameters for method.
     *
     * @param \ReflectionMethod $method
     *
     * @return array|null
     */
    private function getMethodParameters(\ReflectionMethod $method)
    {
        $annotationParameters = $this->getPhpDocReader()->getMethodParameters($method);
        if ($annotationParameters !== null) {
            $methodParameters = [];
            foreach ($annotationParameters as $parameter) {
                if ($this->has($parameter)) {
                    $parameter = $this->get($parameter);
                }
                $methodParameters[] = $parameter;
            }

            return $methodParameters;
        }
    }
}
