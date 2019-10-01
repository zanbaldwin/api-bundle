<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Utility;

class Binary
{
    /**
     * @param string $str
     * @return integer
     */
    public static function getLength(string $str): int
    {
        return \function_exists('mb_strlen')
            ? (int) \mb_strlen($str, '8bit')
            : (int) \strlen($str);
    }

    /**
     * @param string $hex
     * @throws \InvalidArgumentException
     * @return string
     */
    public function fromHex(string $hex): string
    {
        if (!\ctype_xdigit($hex) || static::getLength($hex) % 2 !== 0) {
            throw new \InvalidArgumentException('String is not valid hexadecimal.');
        }
        return \pack('H*', \strtolower($hex));
    }

    /**
     * @param string $binary
     * @return string
     */
    public static function toHex(string $binary): string
    {
        return \unpack('H*', $binary)['hex'];
    }
}
