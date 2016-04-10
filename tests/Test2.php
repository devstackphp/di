<?php

namespace Stack\DI;
/*
 * This file is part of the Stack package.
 *
 * (c) Andrzej Kostrzewa <andkos11@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Stack\DI\D\Test3;

class Test2
{

    /**
     * Test2 constructor.
     */
    public function __construct(Test3 $a, $b=null, $c=null, $d=null, $f=null, $e=null)
    {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
        $this->d = $d;
        $this->f = $f;
        $this->e = $e;
    }
}