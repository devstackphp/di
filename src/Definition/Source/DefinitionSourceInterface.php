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
interface DefinitionSourceInterface
{
    /**
     * Returns the DI definition for the entry name.
     *
     * @param $name
     *
     * @return mixed|null|object
     */
    public function get ($name);
}
