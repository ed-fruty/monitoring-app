<?php

namespace App\Common\Loader;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Parser;

class YamlLoader extends FileLoader
{
    /**
     * @var Parser
     */
    private $yamlParser;

    /**
     * YamlLoader constructor.
     * @param FileLocatorInterface $locator
     * @param Parser $yamlParser
     */
    public function __construct(FileLocatorInterface $locator, Parser $yamlParser)
    {
        parent::__construct($locator);

        $this->yamlParser = $yamlParser;
    }

    /**
     * Loads a resource.
     *
     * @param mixed $resource The resource
     * @param string|null $type The resource type or null if unknown
     *
     * @return mixed
     * @throws \Exception If something went wrong
     */
    public function load($resource, $type = null)
    {
        return $this->yamlParser->parseFile(base_path($resource));
    }

    /**
     * Returns whether this class supports the given resource.
     *
     * @param mixed $resource A resource
     * @param string|null $type The resource type or null if unknown
     *
     * @return bool True if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return 'yml' === pathinfo($resource, PATHINFO_EXTENSION);
    }
}
