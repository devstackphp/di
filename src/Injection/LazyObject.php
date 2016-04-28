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

/**
 * Returns the value of a callable when invoked (thereby invoking the callable).
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
class LazyObject implements LazyInterface
{
    /**
     * The callable to invoke.
     *
     * @var callable
     */
    private $callable;

    /**
     * Arguments for the callable.
     *
     * @var array
     */
    private $params;

    /**
     * LazyObject constructor.
     *
     * @param callable $callable
     * @param array $params
     */
    public function __construct($callable, array $params = [])
    {
        $this->callable = $callable;
        $this->params = $params;
    }

    /**
     * Invokes the closure to create the instance.
     *
     * @return object The object created by the closure.
     */
    public function __invoke()
    {
        if (is_array($this->callable)) {
            foreach ($this->callable as $key => $value) {
                if ($value instanceof LazyInterface) {
                    $this->callable[$key] = $value();
                }
            }
        }

        foreach ($this->params as $key => $value) {
            if ($value instanceof LazyInterface) {
                $this->params[$key] = $value();
            }
        }

        return call_user_func_array($this->callable, $this->params);
    }
}
