<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Formatter\Format;

use Intergalactic\ApiBundle\Exception\RenderException;
use Intergalactic\ApiBundle\Formatter\AbstractFormatter;
use Intergalactic\ApiBundle\Formatter\CollectionInterface;
use Twig\Environment as Twig;

class HTML extends AbstractFormatter
{
    /** @var \Twig\Environment */
    private $twig;
    /** @var \App\Formatter\CollectionInterface */
    private $formatters;

    public function __construct(Twig $twig, CollectionInterface $formatters)
    {
        $this->twig = $twig;
        $this->formatters = $formatters;
    }

    /** {@inheritdoc} */
    public function decode(string $payload)
    {
        throw RenderException::cannotDecodeHtmlPayload();
    }

    /** {@inheritdoc} */
    public function encode($data): string
    {
        // TODO: Render template
    }

    /** {@inheritdoc} */
    public function getFormatName(): string
    {
        return 'html';
    }

    /** {@inheritdoc} */
    public function getSupportedMimeTypes(): array
    {
        return [
            'text/html',
            'application/xhtml',
        ];
    }
}
