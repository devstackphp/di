<?php
/**
 * This file is part of the Stack package.
 *
 * (c) Andrzej Kostrzewa <andkos11@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Stack\DI;

use Stack\DI\Definition\Source\Autowiring;
use Stack\DI\Fixtures\AutowiringFixture;
use Stack\DI\Fixtures\AutowiringFixture2;
use Stack\DI\Fixtures\AutowiringFixtureChild;

class AutowiringTest extends \PHPUnit_Framework_TestCase
{
    private $definitions = [];

    public function testUnknownClass()
    {
        $source = new Autowiring($this->definitions);

        $this->assertNull($source->get('foo'));
    }

    public function testConstructor()
    {
        $source            = new Autowiring($this->definitions);
        $definition        = $source->get('Stack\DI\Fixtures\AutowiringFixture');
        $autowiringFixture = new AutowiringFixture(new AutowiringFixture2());

        $this->assertEquals($autowiringFixture, $definition);
    }

    public function testConstructorInParentClass()
    {
        $source                 = new Autowiring($this->definitions);
        $definition             = $source->get('Stack\DI\Fixtures\AutowiringFixtureChild');
        $autowiringFixtureChild = new AutowiringFixtureChild(new AutowiringFixture2());

        $this->assertEquals($autowiringFixtureChild, $definition);
    }
}
