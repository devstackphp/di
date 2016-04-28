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

use Stack\DI\Exception;
use Stack\DI\Injection\LazyInterface;

/**
 * Resolves class creation specifics based on constructor params and setter
 * definitions, unified across class defaults, inheritance hierarchies, and
 * configuration.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
class Resolver
{
    /**
     * @var array
     */
    protected $definition = [];

    /**
     * Constructor params in the form `$params[$class][$name] = $value`.
     *
     * @var array
     */
    protected $params = [];

    /**
     * Setter definitions in the form of `$setters[$class][$method] = $value`.
     *
     * @var array
     */
    protected $setters = [];

    /**
     * Constructor params and setter definitions, unified across class
     * defaults, inheritance hierarchies, and configuration.
     *
     * @var array
     */
    protected $unifiedClass = [];

    /**
     * Arbitrary values in the form of `$values[$key] = $value`.
     *
     * @var array
     */
    protected $values = [];

    /**
     * A Reflector.
     *
     * @var Reflector
     */
    protected $reflector;

    /**
     * Resolver constructor.
     * @param $reflector
     */
    public function __construct($reflector)
    {
        $this->reflector = $reflector;
    }

    /**
     * @param $definition
     */
    public function add($definition)
    {
        $this->definition = $definition;
    }

    /**
     * Creates and returns a new instance of a class using reflection and
     * the configuration parameters, optionally with overrides, invoking Lazy
     * values along the way.
     *
     * @param string $class The class to instantiate.
     * @param array $mergeParams An array of override parameters.
     * @param array $mergeSetters An array of override setters.
     *
     * @return object
     */
    public function resolve(
        $class,
        array $mergeParams = [],
        array $mergeSetters = []
    ) {
        list($params, $setters) = $this->getUnifiedClass($class);
        $this->mergeParams($class, $params, $mergeParams);
        $this->mergeSetters($class, $setters, $mergeSetters);

        return (object) [
            'reflection' => $this->reflector->getClass($class),
            'params' => $params,
            'setters' => $setters,
        ];
    }

    /**
     * Merges the setters with overrides; also invokes Lazy values.
     *
     * @param string $class The setters are on this class.
     * @param array $setters The class setters.
     * @param array $mergeSetters Override with these setters.
     *
     * @throws Exception\SetterMethodNotFound
     *
     * @return null
     */
    protected function mergeSetters($class, &$setters, array $mergeSetters = [])
    {
        if (!$mergeSetters) {
            return;
        }

        $setters = array_merge($setters, $mergeSetters);
        foreach ($setters as $method => $value) {
            if (!method_exists($class, $method)) {
                throw Exception::setterMethodNotFound($class, $method);
            }

            if ($value instanceof LazyInterface) {
                $setters[$method] = $value();
            }
        }
    }

    /**
     * Merges the params with overrides; also invokes Lazy values.
     *
     * @param string $class The params are on this class.
     * @param array $params The constructor parameters.
     * @param array $mergeParams An array of override parameters.
     *
     * @throws Exception\MissingParam
     *
     * @return array
     */
    protected function mergeParams($class, &$params, array $mergeParams = [])
    {
        if (!$mergeParams) {
            $this->mergeParamsEmpty($class, $params);

            return;
        }

        $positionOfParam = 0;
        foreach ($params as $key => $value) {
            if (array_key_exists($positionOfParam, $mergeParams)) {
                $value = $mergeParams[$positionOfParam];
            } elseif (array_key_exists($key, $mergeParams)) {
                $value = $mergeParams[$key];
            }

            if ($value instanceof UnresolvedParam) {
                throw Exception::missingParam($class, $value->getName());
            }

            if ($value instanceof LazyInterface) {
                $value = $value();
            }

            $params[$key] = $value;

            $positionOfParam += 1;
        }
    }

    /**
     * Load the Lazy values in params when the mergeParams are empty.
     *
     * @param string $class The params are on this class.
     * @param array $params The constructor parameters.
     *
     * @throws Exception\MissingParam
     * @return null
     */
    protected function mergeParamsEmpty($class, &$params)
    {
        foreach ($params as $key => $value) {
            if ($value instanceof UnresolvedParam) {
                throw Exception::missingParam($class, $value->getName());
            }

            if ($value instanceof LazyInterface) {
                $params[$key] = $value();
            }
        }
    }

    /**
     * Returns the unified constructor params and setters for a class.
     *
     * @param string $class The class name to return values for.
     *
     * @return array An array with two elements; 0 is the constructor params
     * for the class, and 1 is the setter methods and values for the class.
     */
    public function getUnifiedClass($class)
    {
        if (isset($this->unifiedClass[$class])) {
            return $this->unifiedClass[$class];
        }

        $unifiedClassElement = [[], []];

        $parent = get_parent_class($class);
        if ($parent) {
            $unifiedClassElement = $this->getUnifiedClass($parent);
        }

        $this->unifiedClass[$class][0] = $this->getUnifiedClassParams($class, $unifiedClassElement[0]);
        $this->unifiedClass[$class][1] = $this->getUnifiedClassSetters($class, $unifiedClassElement[1]);

        return $this->unifiedClass[$class];
    }
    /**
     * Returns the unified constructor params for a class.
     *
     * @param string $class The class name to return values for.
     * @param array $parent The parent unified params.
     *
     * @return array The unified params.
     */
    protected function getUnifiedClassParams($class, array $parent)
    {
        $unifiedParams = [];
        $classParams = $this->reflector->getParameters($class);
        foreach ($classParams as $classParam) {
            $unifiedParams[$classParam->name] = $this->getUnifiedClassParam(
                $classParam,
                $class,
                $parent
            );
        }

        return $unifiedParams;
    }

    /**
     * Returns a unified param.
     *
     * @param \ReflectionParameter $param A parameter reflection.
     * @param string $class The class name to return values for.
     * @param array $parent The parent unified params.
     *
     * @return mixed The unified param value.
     */
    protected function getUnifiedClassParam(\ReflectionParameter $param, $class, $parent)
    {
        $name = $param->getName();
        $position = $param->getPosition();

        if (isset($this->definition[$name])) {
            return $this->definition[$name];
        }

        /**
         * @param self $self
         * @param string $class The class name to return values for.
         * @param integer $position The class param position.
         * @param string $name The class param name.
         *
         * @return mixed The unified param value.
         */
        $explicit = function ($self, $class, $position, $name) {
            $explicitPosition = isset($self->params[$class])
                && array_key_exists($position, $self->params[$class])
                && !$self->params[$class][$position] instanceof UnresolvedParam;

            if ($explicitPosition) {
                return $self->params[$class][$position];
            }

            $explicitNamed = isset($self->params[$class])
                && array_key_exists($name, $self->params[$class])
                && !$self->params[$class][$name] instanceof UnresolvedParam;

            if ($explicitNamed) {
                return $self->params[$class][$name];
            }

            return false;
        };

        /**
         * @param string $name The class name to return values for.
         * @param array $parent The parent unified params.
         * @param \ReflectionParameter $param A parameter reflection.
         *
         * @return mixed The unified param value, or UnresolvedParam.
         */
        $implicitOrDefault = function ($name, $parent, $param) {
            $implicitNamed = array_key_exists($name, $parent)
                && !$parent[$name] instanceof UnresolvedParam;

            if ($implicitNamed) {
                return $parent[$name];
            }

            if ($param->isDefaultValueAvailable()) {
                return $param->getDefaultValue();
            }

            return new UnresolvedParam($name);
        };

        $explicitClass = $explicit($this, $class, $position, $name);
        return $explicitClass ? $explicitClass : $implicitOrDefault($name, $parent, $param);
    }
    /**
     * Returns the unified setters for a class.
     * Class-specific setters take precedence over trait-based setters, which
     * take precedence over interface-based setters.
     *
     * @param string $class The class name to return values for.
     * @param array $parent The parent unified setters.
     *
     * @return array The unified setters.
     */
    protected function getUnifiedClassSetters($class, array $parent)
    {
        $unifiedSetters = $parent;

        $getFromInterfaces = function ($self, $class, &$unifiedSetters) {
            $interfaces = class_implements($class);
            foreach ($interfaces as $interface) {
                if (isset($self->setters[$interface])) {
                    $unifiedSetters = array_merge(
                        $self->setters[$interface],
                        $unifiedSetters
                    );
                }
            }
        };

        $getFromTraits = function ($self, $class, &$unifiedSetters) {
            $traits = $self->reflector->getTraits($class);
            foreach ($traits as $trait) {
                if (isset($self->setters[$trait])) {
                    $unifiedSetters = array_merge(
                        $self->setters[$trait],
                        $unifiedSetters
                    );
                }
            }
        };

        $getFromInterfaces($this, $class, $unifiedSetters);
        $getFromTraits($this, $class, $unifiedSetters);

        if (isset($this->setters[$class])) {
            $unifiedSetters = array_merge(
                $unifiedSetters,
                $this->setters[$class]
            );
        }

        return $unifiedSetters;
    }

    /**
     * Add constructor parameters definition.
     *
     * @param array $params
     */
    public function addParams($params)
    {
        $this->params = array_merge($this->params, $params);
    }

    /**
     * Add setter parameters definition.
     *
     * @param array $setters
     */
    public function addSetters($setters)
    {
        $this->setters = array_merge($this->setters, $setters);
    }
}
