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

class Test4
{
    /**
     * @var \Stack\DI\D\Test3($a)
     */
    public $test;

    /**
     * @var \Stack\DI\Test2($a, [a,b], [c,d], 1, false, [1,2,3])
     */
    public $test2;

}