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

trait FakeChildTrait
{
    use FakeGrandchildTrait;
    protected $child_fake;

    public function setChildFake($fake)
    {
        $this->child_fake = $fake;
    }

    public function getChildFake()
    {
        return $this->child_fake;
    }
}
