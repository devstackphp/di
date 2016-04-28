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

trait FakeGrandchildTrait
{
    protected $grandchild_fake;
    public function setGrandchildFake($fake)
    {
        $this->grandchild_fake = $fake;
    }
    public function getGrandchildFake()
    {
        return $this->grandchild_fake;
    }
}
