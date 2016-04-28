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

/**
 * A placeholder object to indicate a constructor param is missing.
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
class UnresolvedParam
{
    /**
     * The name of the missing param.
     *
     * @var string
     */
    private $name;

    /**
     * UnresolvedParam constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the name of the missing param.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
