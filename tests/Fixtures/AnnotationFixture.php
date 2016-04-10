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

class AnnotationFixture
{
    /**
     * @var
     */
    protected $property1;
    /**
     * @var AnnotationFixture2
     */
    protected $property2;

    /**
     * AnnotationFixture constructor.
     * @param $property1
     * @param $property2
     */
    public function __construct($property1, AnnotationFixture2 $property2)
    {
        $this->property1 = $property1;
        $this->property2 = $property2;
    }


}
