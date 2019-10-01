<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Utility;

class SemVer
{
    public const REGEX = '/^[vV]?(?P<major>0|[1-9]\d*)(?:\.(?P<minor>0|[1-9]\d*)(?:\.(?P<patch>0|[1-9]\d*))?)?$/';

    /**
     * @param string $version
     * @return boolean
     */
    public static function isValid(string $version): bool
    {
        try {
            static::parse($version);
        } catch (\InvalidArgumentException $exception) {
            return false;
        }
        return true;
    }

    /**
     * @param string $version
     * @throws \InvalidArgumentException
     * @return array
     */
    public static function parse(string $version): array
    {
        if (!preg_match(static::REGEX, $version, $matches)) {
            throw new \InvalidArgumentException('Invalid semantic version.');
        }
        return [
            'major' => (int) $matches['major'],
            'minor' => (int) ($matches['minor'] ?? 0),
            'patch' => (int) ($matches['patch'] ?? 0),
        ];
    }

    /**
     * @param string $version
     * @throws \InvalidArgumentException
     * @return string
     */
    public static function sanitise(string $version): string
    {
        $semver = static::parse($version);
        return sprintf('%d.%d.%d', $semver['major'], $semver['minor'], $semver['patch']);
    }
}
