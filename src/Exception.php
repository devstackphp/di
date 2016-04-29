<?php
/**
 * This file is part of the Stack package.
 *
 * (c) Andrzej Kostrzewa <andkos11@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stack\DI;

use Interop\Container\Exception\ContainerException;

/**
 * Generic package exception.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
class Exception extends \Exception implements ContainerException
{
    /**
     * A class constructor param was not defined.
     *
     * @param string $class The class name.
     * @param string $param The constructor param name.
     *
     * @throws Exception\MissingParam
     *
     * @return Exception\MissingParam
     */
    public static function missingParam($class, $param)
    {
        throw new Exception\MissingParam(sprintf('Param missing: %s::$%s', $class, $param));
    }

    /**
     * The container does not have a requested service.
     *
     * @param string $service The service name.
     *
     * @throws Exception\ServiceNotFound
     *
     * @return Exception\ServiceNotFound
     */
    public static function serviceNotFound($service)
    {
        throw new Exception\ServiceNotFound(sprintf("Service not defined: '%s'", $service));
    }

    /**
     * A setter method was defined, but it not available on the class.
     *
     * @param string $class  The class name.
     * @param string $method The method name.
     *
     * @throws Exception\SetterMethodNotFound
     *
     * @return Exception\SetterMethodNotFound
     */
    public static function setterMethodNotFound($class, $method)
    {
        throw new Exception\SetterMethodNotFound(sprintf('Setter method not found: %s::%s()', $class, $method));
    }
}
