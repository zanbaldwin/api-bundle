<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Formatter;

interface EncoderInterface
{
    /** Encodes a string into PHP data. */
    public function encode($data): string;

    /** Checks whether the serializer can encode from given format. */
    public function supportsEncoding(string $format): bool;
}
