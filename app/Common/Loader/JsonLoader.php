<?php

namespace App\Common\Loader;

use Symfony\Component\Config\Loader\FileLoader;

class JsonLoader extends FileLoader
{

    /**
     * Loads a resource.
     *
     * @param mixed $resource The resource
     * @param string|null $type The resource type or null if unknown
     *
     * @return bool|string
     * @throws \Exception If something went wrong
     */
    public function load($resource, $type = null)
    {
        return json_decode(file_get_contents($resource), true);
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
        return 'json' === pathinfo($resource, PATHINFO_EXTENSION);
    }
}
