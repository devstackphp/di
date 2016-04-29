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

class SetterResolver
{
    public function resolve($class, $setters, $mergeSetters = [])
    {
        $this->mergeSetters($class, $setters, $mergeSetters);

        return $setters;
    }

    /**
     * Merges the setters with overrides; also invokes Lazy values.
     *
     * @param string $class        The setters are on this class.
     * @param array  $setters      The class setters.
     * @param array  $mergeSetters Override with these setters.
     *
     * @throws Exception\SetterMethodNotFound
     *
     * @return null
     */
    protected function mergeSetters($class, &$setters, array $mergeSetters = [])
    {
        if (empty($mergeSetters)) {
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
}
