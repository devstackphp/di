<?php
/**
 * This file is part of the Stack package.
 *
 * (c) Andrzej Kostrzewa <andkos11@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stack\DI\Annotation;

use Stack\DI\Autowiring;
use Stack\DI\Exception\ReflectorNotCommentedException;

/**
 * Reads DI class definitions using reflection.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
final class Annotation implements InjectableInterface
{
    /**
     * @var array
     */
    private $annotations = [];

    /**
     * @var array
     */
    private $definitions = [];

    /**
     * Annotation constructor.
     * @param null $reflector
     */
    public function __construct($reflector = null)
    {
        if ($reflector === null) {
            $reflector = [];
        }

        if (!is_array($reflector)) {
            $docComment = self::parseDocComment($reflector);
            $reflector = AnnotationParser::getAnnotations($docComment);
        }

        $this->annotations = $reflector;
    }

    /**
     * {@inheritdoc}
     */
    public function add(array $definitions)
    {
        $this->definitions = $definitions;
    }

    /**
     * Get annotations array.
     *
     * @return array
     */
    public function asArray()
    {
        return $this->annotations ?: [];
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

        $class = new \ReflectionClass($name);
        $namespace = $class->getNamespaceName();
        $object = $class->newInstanceWithoutConstructor();
        foreach ($class->getProperties() as $property) {
            $propertyClass = $this->createPropertyClass($namespace, $property);
            $this->injectProperty($property, $object, $propertyClass);
        }

        $this->set($name, $object);

        return $object;
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
     * Create Class for property.
     *
     * @param $namespace
     * @param $property
     * @return object
     */
    private function createPropertyClass($namespace, $property)
    {
        $self = new Annotation($property);
        $annotationArray = $self->asArray();

        if (empty($annotationArray)) {
            return null;
        }

        $createClass = function ($namespace, $annotationArray) {
            $propertyClassName = key($annotationArray);

            if (AnnotationParser::hasNamespace()) {
                $propertyClassName = $namespace.'\\'.$propertyClassName;
            }

            if (is_bool(current($annotationArray))) {
                return new $propertyClassName();
            }

            $classParameters = $annotationArray[$propertyClassName];
            if (is_array($classParameters)) {
                return new $propertyClassName($classParameters);
            }

            return $this->createPropertyClassWithParameters($propertyClassName, $classParameters);
        };

        return $createClass($namespace, $annotationArray);
    }

    /**
     * Create Class for property with parameters.
     *
     * @param $propertyClassName
     * @param $classParameters
     * @return mixed|object
     */
    private function createPropertyClassWithParameters($propertyClassName, $classParameters)
    {
        $autowired = new Autowiring();
        if ($autowired->has($propertyClassName)) {
            return $autowired->get($propertyClassName);
        }

        $matches = [];
        $commaPosition = strpos($classParameters, ',');
        if ($commaPosition !== false) {
            preg_match_all(
                '/\[([a-zA-Z0-9,]+)\]/',
                trim($classParameters),
                $matches,
                PREG_SET_ORDER
            );

            $classParameters = explode(',', $classParameters);

            $k = 0;
            $end = true;
            $parameters = [];
            foreach ($classParameters as $value) {
                $beginArrayPosition = strpos($value, '[');
                $endArrayPosition = strpos($value, ']');

                if ($end === true && $beginArrayPosition === false && $endArrayPosition === false) {
                    $parameters[] = trim($value);
                } elseif ($endArrayPosition !== false) {
                    $parameters[] = $matches[$k][0];
                    $k++;
                    $end = true;
                } else {
                    $end = false;
                }
            }

            $classParameters = $parameters;
        }

        $class = new \ReflectionClass($propertyClassName);
        $autowiredClassParameters = $autowired->getParametersDefinition($class->getConstructor());
        $i = count($classParameters) - 1;
        $i = $i > 0 ? $i : 1;
        $j = count($autowiredClassParameters);

        do {
            $value = $classParameters[$j];
            $autowiredClassParameters[] = AnnotationParser::parseValue($value);
            $j++;
            --$i;
        } while ($i);

        return $class->newInstanceArgs($autowiredClassParameters);
    }

    /**
     * Inject value for property.
     *
     * @param \ReflectionProperty $property
     * @param $object
     * @param $target
     */
    private function injectProperty(\ReflectionProperty $property, $object, $target)
    {
        $property->setAccessible(true);
        $property->setValue($object, $target);
    }

    /**
     * Parse comment from document.
     *
     * @param $reflection
     * @return string
     * @throws ReflectorNotCommentedException
     */
    private static function parseDocComment($reflection)
    {
        if (is_object($reflection)) {
            if (!($reflection instanceof \Reflector)) {
                throw new \InvalidArgumentException();
            }

            if (!method_exists($reflection, 'getDocComment')) {
                throw new ReflectorNotCommentedException("Only Reflector implementations that provide a" .
                    "getDocComment() method can be parsed for annotations");
            }

            return $reflection->getDocComment();
        }

        if (class_exists($reflection)) {
            $class = new \ReflectionClass($reflection);

            return $class->getDocComment();
        }

        return $reflection;
    }
}
