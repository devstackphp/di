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

use Stack\DI\Injection\LazyNewObject;

/**
 * This extension of the Resolver additionally auto-resolves unspecified
 * constructor params according to their typehints.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
class AutoResolver extends Resolver
{
    /**
     * Auto-resolves params typehinted to classes.
     *
     * @param \ReflectionParameter $param  A parameter reflection.
     * @param string               $class  The class name to return values for.
     * @param array                $parent The parent unified params.
     *
     * @return mixed The auto-resolved param value, or UnresolvedParam.
     */
    protected function getUnifiedClassParam(\ReflectionParameter $param, $class, $parent)
    {
        $unifiedClassParam = parent::getUnifiedClassParam($param, $class, $parent);

        if (!$unifiedClassParam instanceof UnresolvedParam) {
            return $unifiedClassParam;
        }

        /*
         * @param AutoResolver $self
         * @param \ReflectionParameter $param
         * @param mixed $unifiedClassParam
         *
         * @return mixed
         */
        $getDefinition = function ($self, $param, $unifiedClassParam) {
            $definition = $param->getClass();

            if ($definition && isset($this->definition[$definition->name])) {
                return $this->definition[$definition->name];
            }

            if ($definition && $definition->isInstantiable()) {
                return new LazyNewObject($self, $definition->name);
            }

            return $unifiedClassParam;
        };

        return $getDefinition($this, $param, $unifiedClassParam);
    }
}
