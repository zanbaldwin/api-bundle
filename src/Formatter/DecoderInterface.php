<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Formatter;

interface DecoderInterface
{
    /** Decodes a string into PHP data. */
    public function decode(string $payload);

    /** Checks whether the deserializer can decode from given format. */
    public function supportsDecoding(string $format);
}
