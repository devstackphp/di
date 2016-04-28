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
 * Indicates a Lazy to be invoked when resolving params and setters.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
interface LazyInterface
{
    /**
     * Invokes the Lazy to return a result, usually an object.
     *
     * @return mixed
     */
    public function __invoke();
}
