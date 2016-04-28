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

/**
 * Defines an alias from an entry to another.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
class Alias
{
    /**
     * Entry name.
     *
     * @var null|string
     */
    private $name;

    /**
     * Name of the target entry.
     *
     * @var string
     */
    private $targetName;

    /**
     * Alias constructor.
     *
     * @param string $targetName
     * @param null|string $name
     */
    public function __construct($targetName, $name = null)
    {
        $this->name = $name;
        $this->targetName = $targetName;
    }

    /**
     * @return null|string Entry name.
     */
    public function getName()
    {
        if ($this->name === null) {
            $name       = explode('\\', $this->targetName);
            $name       = end($name);
            $this->name = $name;
        }

        return $this->name;
    }

    /**
     * @return string Name of the target entry.
     */
    public function getTargetName()
    {
        return $this->targetName;
    }
}
