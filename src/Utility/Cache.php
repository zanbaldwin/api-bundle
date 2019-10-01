<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Utility;

class Cache
{
    protected const VALID_CACHE_CHARACTERS = 'a-zA-Z\d_\.';
    protected const SEPARATOR_CHARACTER = '_';

    public static function convertStringToSafeCacheKey(string $key): string
    {
        return preg_replace(
            '/' . preg_quote(static::SEPARATOR_CHARACTER) . '{2,}/',
            static::SEPARATOR_CHARACTER,
            preg_replace(
                '/[^' . static::VALID_CACHE_CHARACTERS . ']/',
                static::SEPARATOR_CHARACTER,
                $key
            )
        );
    }
}
