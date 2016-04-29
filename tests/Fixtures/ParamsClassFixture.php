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

class ParamsClassFixture
{
    public $array;
    public $empty = 'not null';

    public function __construct(array $array, $empty)
    {
        $this->array = $array;
        $this->empty = null;
    }
}