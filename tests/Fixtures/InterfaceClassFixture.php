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

class InterfaceClassFixture implements InterfaceFixture
{
    protected $foo;

    public function setFoo($foo)
    {
        $this->foo = $foo;

        return $this;
    }

    public function getFoo()
    {
        return $this->foo;
    }
}
