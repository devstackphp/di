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

use Interop\Container\ContainerInterface;
use Stack\DI\Definition\Source\Annotation;
use Stack\DI\Definition\Source\Autowiring;

/**
 * Helper to create and configure a Container.
 *
 * With the default options, the container created is appropriate for the development environment.
 *
 * Example:
 *
 *     $builder = new ContainerBuilder();
 *     $container = $builder->build();
 *
 * @author Andrzej Kostrzewa <andkos11@gmail.com>
 */
class ContainerBuilder
{
    /**
     * @var string
     */
    private $containerClass;

    /**
     * @var bool
     */
    private $useAutowiring = true;

    /**
     * @var bool
     */
    private $useAnnotation = false;

    /**
     * @var array
     */
    private $definitionSources = [];

    /**
     * @var ContainerInterface
     */
    private $delegateContainer;

    /**
     * ContainerBuilder constructor.
     *
     * @param string $containerClass
     */
    public function __construct($containerClass = 'Stack\DI\Container')
    {
        $this->containerClass = $containerClass;
    }

    /**
     * Add definitions to the container.
     *
     * @param array $definitions
     *
     * @return bool
     */
    public function addDefinitions(array $definitions)
    {
        $this->definitionSources = $definitions;

        return $this;
    }

    /**
     * Build and return a container.
     *
     * @return Container
     */
    public function build()
    {
        $definitionSource = null;
        if ($this->useAnnotation) {
            $definitionSource = new Annotation($this->definitionSources);
        } elseif ($this->useAutowiring) {
            $definitionSource = new Autowiring($this->definitionSources);
        }

        $containerClass = $this->containerClass;

        return new $containerClass($definitionSource, $this->delegateContainer);
    }

    /**
     * Build a container configured for the dev environment.
     *
     * @return Container
     */
    public static function buildDevContainer()
    {
        $builder = new self();

        return $builder->build();
    }

    /**
     * Enable or disable the use of autowiring to guess injections.
     *
     * Enabled by default.
     *
     * @param $bool
     *
     * @return $this
     */
    public function useAutowiring($bool)
    {
        $this->useAutowiring = $bool;

        return $this;
    }

    /**
     * Enable or disable the use of annotations to guess injections.
     *
     * Disabled by default.
     *
     * @param $bool
     *
     * @return $this
     */
    public function useAnnotation($bool)
    {
        $this->useAnnotation = $bool;

        return $this;
    }

    /**
     * Delegate the container for dependencies.
     *
     * @param ContainerInterface $delegateContainer
     *
     * @return $this
     */
    public function setDelegateContainer(ContainerInterface $delegateContainer)
    {
        $this->delegateContainer = $delegateContainer;

        return $this;
    }
}
