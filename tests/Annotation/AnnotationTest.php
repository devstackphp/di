<?php
/**
 * This file is part of the Stack package.
 *
 * (c) Andrzej Kostrzewa <andkos11@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stack\DI\Annotation;

use Stack\DI\Fixtures\AnnotationFixture;
use Stack\DI\Fixtures\AnnotationFixture2;

class AnnotationTest extends \PHPUnit_Framework_TestCase
{
    public function testUnknownClass()
    {
        $source = new Annotation();
        $this->assertNull($source->get('foo'));
    }

    public function testProperty()
    {
        $source = new Annotation();
        $definition = $source->get('Stack\DI\Fixtures\AnnotationFixture');
        $annotationFixture = new AnnotationFixture(null, new AnnotationFixture2());
        
        $this->assertEquals($annotationFixture, $definition);
    }
}
