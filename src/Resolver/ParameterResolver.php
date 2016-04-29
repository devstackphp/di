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
 * Class ParameterResolver
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
class ParameterResolver
{
    /**
     * Resolve class constructor params
     * 
     * @param $class
     * @param $params
     * @param array $mergeParams
     *
     * @throws Exception\MissingParam
     *
     * @return mixed
     */
    public function resolve($class, $params, $mergeParams = [])
    {
        if (empty($mergeParams)) {
            $this->mergeParamsEmpty($class, $params);

            return $params;
        }

        $this->mergeParams($class, $params, $mergeParams);

        return $params;
    }

    /**
     * Merges the params with overrides; also invokes Lazy values.
     *
     * @param string $class       The params are on this class.
     * @param array  $params      The constructor parameters.
     * @param array  $mergeParams An array of override parameters.
     *
     * @throws Exception\MissingParam
     *
     * @return string[]|null
     */
    protected function mergeParams($class, &$params, array $mergeParams = [])
    {
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

            $positionOfParam++;
        }
    }

    /**
     * Load the Lazy values in params when the mergeParams are empty.
     *
     * @param string $class  The params are on this class.
     * @param array  $params The constructor parameters.
     *
     * @throws Exception\MissingParam
     *
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
}
