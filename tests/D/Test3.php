<?php

namespace Stack\DI\D;
/*
 * This file is part of the Stack package.
 *
 * (c) Andrzej Kostrzewa <andkos11@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Stack\DI\Test;

class Test3
{

    /**
     * Test3 constructor.
     */
    public function __construct(Test $a)
    {
        $this->a=$a;
    }

    public function tet()
    {
        return true;
    }
}