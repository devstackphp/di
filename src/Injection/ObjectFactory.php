<?php
/**
 * This file is part of the Stack package.
 *
 * (c) Andrzej Kostrzewa <andkos11@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stack\DI\Injection;

use Stack\DI\Resolver\Resolver;

/**
 * A generic factory to create repeated instances of a single class.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
class ObjectFactory
{
    /**
     * The class to create.
     *
     * @var string
     */
    protected $class;

    /**
     * Override params for the class.
     *
     * @var array
     */
    protected $params;

    /**
     * The Resolver.
     *
     * @var Resolver
     */
    protected $resolver;

    /**
     * Override setters for the class.
     *
     * @var array
     */
    protected $setters;

    /**
     * ObjectFactory constructor.
     *
     * @param Resolver $resolver
     * @param string   $class
     * @param array    $params
     * @param array    $setters
     */
    public function __construct(
        Resolver $resolver,
        $class,
        array $params = [],
        array $setters = []
    ) {
        $this->resolver = $resolver;
        $this->class    = $class;
        $this->params   = $params;
        $this->setters  = $setters;
    }

    /**
     * Invoke the Factory object as a function to use the Factory to create
     * a new instance of the specified class.
     *
     * @return object
     */
    public function __invoke()
    {
        $params  = array_merge($this->params, func_get_args());
        $resolve = $this->resolver->resolve(
            $this->class,
            $params,
            $this->setters
        );

        $object = $resolve->reflection->newInstanceArgs($resolve->params);
        foreach ($resolve->setters as $method => $value) {
            $object->$method($value);
        }

        return $object;
    }
}
