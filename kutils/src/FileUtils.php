<?php


namespace Kutils;


use RuntimeException;
use function count;

trait FileUtils
{
    /**
     * @param array $resources
     * @return int Index of the resource, by name
     */
    public static function findResourceByName(array $resources, string $name, string $type): int
    {
        foreach ($resources as $i => $resource) {
            if (isset($resource['kind']) && $resource['kind'] === $type && isset($resource['metadata']['name']) && $resource['metadata']['name'] === $name) {
                return $i;
            }
        }
        throw new RuntimeException('Could not find "'.$type.'" with name "'.$name.'"');
    }

    /**
     * @param array $resources
     * @return int Index of the resource, by name
     */
    public static function findResourceByType(array $resources, string $type): int
    {
        $foundResources = [];
        foreach ($resources as $i => $resource) {
            if (isset($resource['kind']) && $resource['kind'] === $type) {
                $foundResources[$i] = $resource;
            }
        }
        if (count($foundResources) === 0) {
            throw new RuntimeException('Could not find resource with type "'.$type.'"');
        }
        if (count($foundResources) > 1) {
            throw new RuntimeException('There are many resources with type "'.$type.'" in the YAML document');
        }
        foreach ($resources as $i => $resource) {
            return $i;
        }
    }
}