<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Formatter;

interface FormatterInterface extends EncoderInterface, DecoderInterface
{
    /**
     * @param string $format
     * @return boolean
     */
    public function supportsFormat(string $format): bool;

    /**
     * @return string
     */
    public function getFormatName(): string;

    /**
     * @param string $mimeType
     * @return boolean
     */
    public function supportsMimeType(string $mimeType): bool;

    /**
     * @return array
     */
    public function getSupportedMimeTypes(): array;
}
