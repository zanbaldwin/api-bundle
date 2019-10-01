<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Formatter;

abstract class AbstractFormatter implements FormatterInterface
{
    /** {@inheritdoc} */
    final public function supportsDecoding(string $format): bool
    {
        return $this->supportsFormat($format);
    }

    /** {@inheritdoc} */
    final public function supportsEncoding(string $format): bool
    {
        return $this->supportsFormat($format);
    }

    /** {@inheritdoc} */
    final public function supportsFormat(string $format): bool
    {
        return $this->getFormatName() === $format;
    }

    /** {@inheritdoc} */
    final public function supportsMimeType(string $mimeType): bool
    {
        return in_array($mimeType, $this->getSupportedMimeTypes(), true);
    }
}
