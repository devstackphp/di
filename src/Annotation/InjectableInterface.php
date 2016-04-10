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

/**
 * Reads DI interface.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
interface InjectableInterface
{
    /**
     * Add definitions to the DI.
     *
     * @param array $definitions
     */
    public function add(array $definitions);

    /**
     * Returns the DI definition for the entry name.
     *
     * @param $name
     * @return mixed|null|object
     */
    public function get($name);

    /**
     * Returns true if the DI can return an entry for the given name.
     * Returns false otherwise.
     *
     * @param $name
     * @return bool
     */
    public function has($name);

    /**
     * Set the DI definition for the entry name.
     *
     * @param $name
     * @param $value
     */
    public function set($name, $value);
}
