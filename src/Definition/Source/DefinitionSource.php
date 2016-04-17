<?php
/**
 * This file is part of the Stack package.
 *
 * (c) Andrzej Kostrzewa <andkos11@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stack\DI\Definition\Source;


/**
 * Source of definitions for entries of the container.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
abstract class DefinitionSource implements DefinitionSourceInterface
{
    /**
     * @var array
     */
    protected $definitions = [];

    /**
     * DefinitionSource constructor.
     *
     * @param array $definitions
     */
    public function __construct(array $definitions)
    {
        $this->definitions = $definitions;
    }

    /**
     * Add definitions to the DI.
     *
     * @param array $definitions
     */
    public function add(array $definitions)
    {
        $this->definitions = array_merge($this->definitions, $definitions);
    }

    /**
     * Returns true if the DI can return an entry for the given name.
     * Returns false otherwise.
     *
     * @param $name
     *
     * @return bool
     */
    public function has($name)
    {
        return isset($this->definitions[$name]) || array_key_exists($name, $this->definitions);
    }

    /**
     * Set the DI definition for the entry name.
     *
     * @param $name
     * @param $value
     */
    public function set($name, $value)
    {
        $this->definitions[$name] = $value;
    }
}
