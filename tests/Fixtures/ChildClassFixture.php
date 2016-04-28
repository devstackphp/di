<?php
/**
 * This file is part of the Stack package.
 *
 * (c) Andrzej Kostrzewa <andkos11@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stack\DI\Fixtures;

class ChildClassFixture extends ParentClassFixture
{
    protected $other;
    protected $fake;

    public function __construct($foo, $other = null)
    {
        parent::__construct($foo);
        $this->other = $other;
    }

    public function setFake($fake)
    {
        $this->fake = $fake;
    }

    public function getFake()
    {
        return $this->fake;
    }

    public function getOther()
    {
        return $this->other;
    }
}
