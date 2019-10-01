<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Formatter;

use Symfony\Component\HttpFoundation\Request;

class FormatterCollection implements CollectionInterface, \ArrayAccess
{
    /** @var \App\Formatter\FormatterInterface[] */
    private $formatters = [];

    /** @throws \InvalidArgumentException */
    public function __construct(array $formatterClasses = [])
    {
        foreach ($formatterClasses as $formatter) {
            $this->registerFormatter($formatter);
        }
    }

    /** {@inheritdoc} */
    public function registerFormatter(FormatterInterface $formatter): void
    {
        if ($this->supportsFormat($formatter->getFormatName())) {
            throw new \InvalidArgumentException(sprintf(
                'Formatter that supports "%s" format already registered with collection.',
                $formatter->getFormatName()
            ));
        }
        $this->formatters[$formatter->getFormatName()] = $formatter;
    }

    /** {@inheritdoc} */
    public function registerFormatsWithRequest(Request $request): void
    {
        foreach ($this->formatters as $formatter) {
            $request->setFormat(
                $formatter->getFormatName(),
                $formatter->getSupportedMimeTypes()
            );
        }
    }

    /** {@inheritdoc} */
    public function getSupportedFormats(): array
    {
        return array_keys($this->formatters);
    }

    /** {@inheritdoc} */
    public function supportsFormat(string $format): bool
    {
        return isset($this->formatters[$format]);
    }

    /** {@inheritdoc} */
    public function getSupportedMimeTypes(): array
    {
        $mimeTypes = [];
        /** @var \App\Formatter\FormatterInterface $formatter */
        foreach ($this->formatters as $formatter) {
            $mimeTypes = array_merge($mimeTypes, $formatter->getSupportedMimeTypes());
        }
        return array_unique($mimeTypes);
    }

    /** {@inheritdoc} */
    public function supportsMimeType(string $mimeType): bool
    {
        return in_array($mimeType, $this->getSupportedMimeTypes(), true);
    }

    /** {@inheritdoc} */
    public function determineFormatFromMimeType(string $mimeType): string
    {
        foreach ($this->formatters as $formatter) {
            if ($formatter->supportsMimeType($mimeType)) {
                return $formatter->getFormatName();
            }
        }
        throw new \InvalidArgumentException(sprintf('"%s" is not a supported MIME type', $mimeType));
    }

    /** {@inheritdoc} */
    public function getForFormat(string $format): FormatterInterface
    {
        if (!$this->supportsFormat($format)) {
            throw new \InvalidArgumentException(sprintf(
                'Formatter supporting "%s" format does not exist in collection.',
                $format
            ));
        }
        return $this->formatters[$format];
    }

    /** {@inheritdoc} */
    public function getForMimeType(string $mimeType): FormatterInterface
    {
        foreach ($this->formatters as $formatter) {
            if ($formatter->supportsMimeType($mimeType)) {
                return $formatter;
            }
        }
        throw new \InvalidArgumentException(sprintf(
            'Formatter supporting "%s" MIME type does not exist in collection.',
            $mimeType
        ));
    }

    /** {@inheritdoc} */
    public function offsetExists($offset)
    {
        return $this->supportsFormat($offset);
    }

    /** {@inheritdoc} */
    public function offsetGet($offset)
    {
        return $this->getForFormat($offset);
    }

    /** {@inheritdoc} */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('Cannot directly set formatter; please registerFormatter() instead.');
    }

    /** {@inheritdoc} */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('Cannot deregister formatter.');
    }
}
