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

class AutowiringFixture
{
    public function __construct(AutowiringFixture2 $param1, $param2 = null)
    {
        $this->param1 = $param1;
        $this->param2 = $param2;
    }
}
