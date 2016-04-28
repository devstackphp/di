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

use Stack\DI\Definition\Source\Annotation;
use Stack\DI\Fixtures\AnnotationFixture;
use Stack\DI\Fixtures\AnnotationFixture2;

/**
 * @covers Stack\DI\Definition\Source\Annotation
 */
class AnnotationTest extends \PHPUnit_Framework_TestCase
{
    private $definitions = [];

    public function testUnknownClass()
    {
        $source = new Annotation($this->definitions);
        $this->assertNull($source->get('foo'));
    }

    public function testProperty()
    {
        $source            = new Annotation($this->definitions);
        $definition        = $source->get('Stack\DI\Fixtures\AnnotationFixture');
        $annotationFixture = new AnnotationFixture('foo', new AnnotationFixture2(1, 2));
        $annotationFixture->setProperty3('bar');

        $this->assertEquals($annotationFixture, $definition);
    }
}
