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
use Stack\DI\Exception\AnnotationException;

/**
 * PhpDoc reader.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
class PhpDocReader
{
    /**
     * @var array
     */
    private static $ignoredTypes = [
        'bool',
        'boolean',
        'string',
        'int',
        'integer',
        'float',
        'double',
        'array',
        'object',
        'callable',
        'resource',
        '',
    ];

    /**
     * @var string
     */
    private $namespace;

    /**
     * Parse the docblock of the property to get the parameters of the param annotation.
     *
     * @param \ReflectionMethod $method
     *
     * @return array|null
     */
    public function getMethodParameters(\ReflectionMethod $method)
    {
        $methodComment = $method->getDocComment();
        if (preg_match_all('/@param\s+([^\s\*\/]+)/', $methodComment, $matches)) {
            $classNames = end($matches);
        } else {
            return null;
        }

        $parameters = [];
        foreach ($classNames as $type) {
            $values = explode('(', $type);
            if (in_array($values[0], PhpDocReader::$ignoredTypes)) {
                $value = end($values);
                $value = trim($value, ') ');
                $type = $this->parseValue($value);
            }
            $parameters[] = $type;
        }

        return $parameters;
    }

    /**
     * Parse the docblock of the property to get the class of the var annotation.
     *
     * @param \ReflectionProperty $property
     *
     * @throws AnnotationException Non exists class.
     *
     * @return null|ObjectDefinition
     *
     */
    public function getPropertyClass(\ReflectionProperty $property)
    {
        $propertyComment = $property->getDocComment();
        if (preg_match('/@var\s+([^\s\(\*\/]+)/', $propertyComment, $matches)) {
            $className = end($matches);
        } else {
            return null;
        }

        if (in_array($className, PhpDocReader::$ignoredTypes)) {
            return null;
        }

        $classNameWithNamespace = $className;
        if ($this->namespaceExists($classNameWithNamespace) === false) {
            $classNameWithNamespace = $this->namespace . '\\' . $className;
        }

        if (!$this->classExists($classNameWithNamespace)) {
            $declaringClass = $property->getDeclaringClass();
            throw new AnnotationException(sprintf(
                'The @var annotation on %s::%s contains a non existent class "%s"',
                $declaringClass->name,
                $property->getName(),
                $className
            ));
        }

        $classParameters = $this->propertyClassParameters($propertyComment, $className);
        if ($classParameters !== null) {
            $values = [];
            foreach ($classParameters as $value) {
                $values[] = $this->parseValue($value);
            }

            $object = new ObjectDefinition($classNameWithNamespace, $className);
            $object->setConstructorInjection($values);

            return $object;
        }

        return new $classNameWithNamespace;
    }

    /**
     * @param $className
     *
     * @return bool
     */
    private function classExists($className)
    {
        return class_exists($className) || interface_exists($className);
    }

    /**
     * @param $className
     *
     * @return bool|int
     */
    private function namespaceExists($className)
    {
        return strpos($className, $this->namespace);
    }

    /**
     * Parse value by type and return.
     *
     * @param $value
     *
     * @return array|bool|float|int|string
     */
    private function parseValue($value)
    {
        $value = trim($value, ', ');

        $isNumberOrBool = function (&$value) {
            if (is_numeric($value)) {
                $value = (float) $value;

                if ((float) $value == (int) $value) {
                    $value = (int) $value;
                }

                return true;
            }

            $isBool = function (&$value) {
                if (strtolower($value) == 'true') {
                    $value = true;

                    return true;
                }

                if (strtolower($value) == 'false') {
                    $value = false;

                    return true;
                }

                return false;
            };

            return $isBool($value);
        };

        $isArrayOrOther = function (&$value) {
            if (substr($value, 0, 1) === '[' && substr($value, -1) === ']') {
                $valuesArray = explode(',', substr($value, 1, -1));
                $value = [];
                foreach ($valuesArray as $val) {
                    $value[] = self::parseValue($val);
                }

                return true;
            }

            if (substr($value, 0, 1) == '"' && substr($value, -1) == '"' ||
                substr($value, 0, 1) == '\'' && substr($value, -1) == '\'') {
                $value = substr($value, 1, -1);
                $value = self::parseValue($value);

                return true;
            }

            return false;
        };

        if ($isArrayOrOther($value)) {
            return $value;
        }

        if ($isNumberOrBool($value)) {
            return $value;
        }

        return $value;
    }

    /**
     * Get property class parameters.
     *
     * @param $property
     * @param $className
     *
     * @return array|null
     */
    private function propertyClassParameters($property, $className)
    {
        $classNamePosition = strpos($property, $className);
        if ($classNamePosition !== false) {
            $classNameLength = mb_strlen($className);
            $property = ltrim(substr($property, $classNamePosition + $classNameLength ));
            if (preg_match_all('/([\w,$\[\]\'\"]+)/', $property , $matches)) {
                return end($matches);
            }
        }

        return null;
    }

    /**
     * Set default namespace.
     *
     * @param $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }
}
