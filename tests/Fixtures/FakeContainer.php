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


use Interop\Container\ContainerInterface;
use Stack\DI\Definition\Source\DefinitionSourceInterface;

class FakeContainer
{
    /**
     * @var DefinitionSourceInterface
     */
    public $definitionSource;

    /**
     * @var ContainerInterface
     */
    public $delegateContainer;

    public function __construct(
        DefinitionSourceInterface $definitionSource,
        ContainerInterface $delegateContainer = null
    ) {
        $this->definitionSource = $definitionSource;
        $this->delegateContainer = $delegateContainer;
    }
}