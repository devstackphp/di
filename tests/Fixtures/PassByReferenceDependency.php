<?php
/*
 * This file is part of the Stack package.
 *
 * (c) Andrzej Kostrzewa <andkos11@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stack\DI\Fixtures;

use stdClass;

class PassByReferenceDependency
{
    public function __construct(stdClass &$object)
    {
        $object->foo = 'bar';
    }
}