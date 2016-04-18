<?php
/**
 * This file is part of the Stack package.
 *
 * (c) Andrzej Kostrzewa <andkos11@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Stack\DI\Definition;

/**
 * Defines an alias from an entry to another.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
class AliasDefinition
{
    /**
     * Entry name.
     *
     * @var string|null
     */
    private $name;

    /**
     * Name of the target entry.
     *
     * @var string|null
     */
    private $targetName;

    /**
     * AliasDefinition constructor.
     *
     * @param string $name       Entry name
     * @param string $targetName Name of the target entry
     */
    public function __construct($name = null, $targetName = null)
    {
        $this->name = $name;
        $this->targetName = $targetName;
    }

    /**
     * Extract name from target entry.
     *
     * @param $targetName
     */
    public function aliasFromNamespace($targetName)
    {
        $name             = explode('\\', $targetName);
        $name             = end($name);
        $this->name       = strtolower($name);
        $this->targetName = $targetName;
    }

    /**
     * @return string Entry name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string Name of the target entry
     */
    public function getTargetName()
    {
        return $this->targetName;
    }
}
