<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Utility;

class Path
{
    public static function normalize(string $relative, string $root = '.'): string
    {
        $segments = [];
        $path = strpos($relative, '/') !== 0 ? $root . '/' . $relative : $relative;
        $isAbsolute = strpos($path, '/') === 0;
        foreach (explode('/', $path) as $part) {
            switch (true) {
                case empty($part) || $part === '.':
                    break;
                case $part !== '..':
                    array_push($segments, $part);
                    break;
                case count($segments) > 0:
                    array_pop($segments);
                    break;
                default:
                    throw new \RuntimeException(
                        $isAbsolute
                            ? 'Cannot reference paths above root directory.'
                            : 'Not enough hierarchical information in relative path to normalize.'
                    );
            }
        }
        $resolved = implode('/', $segments);
        return $isAbsolute ? '/' . $resolved : $resolved;
    }
}
