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

/**
 * A serializable collection point for for Reflection data.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
class Reflector
{
    /**
     * Collected ReflectionClass instances.
     *
     * @var array
     */
    private $classCollections = [];

    /**
     * Collected arrays of ReflectionParameter instances for class constructors.
     *
     * @var array
     */
    private $parameters = [];

    /**
     * Collected traits in classes.
     *
     * @var array
     */
    private $traits = [];

    /**
     * When serializing, ignore the Reflection-based properties.
     *
     * @return string[]
     */
    public function __sleep()
    {
        return ['traits'];
    }

    /**
     * Returns a ReflectionClass for the given class.
     *
     * @param string $class
     *
     * @throws Exception\ServiceNotFound
     *
     * @return \ReflectionClass
     */
    public function getClass($class)
    {
        if (isset($this->classCollections[$class])) {
            return $this->classCollections[$class];
        }

        if (!class_exists($class) && !interface_exists($class)) {
            throw Exception::serviceNotFound($class);
        }

        $this->classCollections[$class] = new \ReflectionClass($class);

        return $this->classCollections[$class];
    }

    /**
     * Returns an array of ReflectionParameter instances for the constructor of
     * a given class.
     *
     * @param $class
     *
     * @return array
     */
    public function getParameters($class)
    {
        if (isset($this->parameters[$class])) {
            return $this->parameters[$class];
        }

        $this->parameters[$class] = [];
        $constructor              = $this->getClass($class)->getConstructor();
        if ($constructor) {
            $this->parameters[$class] = $constructor->getParameters();
        }

        return $this->parameters[$class];
    }

    /**
     * Returns all traits used by a class and its ancestors,
     * and the traits used by those traits' and their ancestors.
     *
     * @param $class
     *
     * @return mixed
     */
    public function getTraits($class)
    {
        if (isset($this->traits[$class])) {
            return $this->traits[$class];
        }

        $traits = [];
        do {
            $traits += class_uses($class);
            $class   = get_parent_class($class);
        } while ($class);

        while (list($trait) = each($traits)) {
            foreach (class_uses($trait) as $key => $name) {
                $traits[$key] = $name;
            }
        }

        $this->traits[$class] = $traits;

        return $this->traits[$class];
    }
}
