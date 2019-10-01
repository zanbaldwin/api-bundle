<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Formatter;

use Symfony\Component\HttpFoundation\Request;

interface CollectionInterface
{
    /**
     * @param \App\Formatter\FormatterInterface $formatters
     * @throws \InvalidArgumentException
     * @return void
     */
    public function registerFormatter(FormatterInterface $formatters): void;

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @reutrn void
     */
    public function registerFormatsWithRequest(Request $request): void;

    /**
     * @return string[]
     */
    public function getSupportedFormats(): array;

    /**
     * @param string $format
     * @return boolean
     */
    public function supportsFormat(string $format): bool;

    /**
     * @return string[]
     */
    public function getSupportedMimeTypes(): array;

    /**
     * @param string $mimeType
     * @return boolean
     */
    public function supportsMimeType(string $mimeType): bool;

    /**
     * @param string $mimeType
     * @throws \InvalidArgumentException
     * @return string
     */
    public function determineFormatFromMimeType(string $mimeType): string;

    /**
     * @param string $format
     * @throws \InvalidArgumentException
     * @return \App\Formatter\FormatterInterface
     */
    public function getForFormat(string $format): FormatterInterface;

    /**
     * @param string $mimeType
     * @throws \InvalidArgumentException
     * @return \App\Formatter\FormatterInterface
     */
    public function getForMimeType(string $mimeType): FormatterInterface;
}
