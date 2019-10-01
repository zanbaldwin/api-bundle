<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class RenderException extends HttpException implements DescriptiveHttpExceptionInterface
{
    /** @var string|null */
    private $template;
    /** @var array */
    private $additionalInformation;

    public function __construct(
        ?string $template,
        string $message,
        ?\Throwable $previous = null,
        array $additionalInformation = [],
        array $headers = []
    ) {
        $this->template = $template;
        parent::__construct(500, $message, $previous, $headers, 0);
        $this->additionalInformation = $additionalInformation;
    }

    public static function nonExistentTemplate(
        string $template,
        array $availableMimeTypes,
        ?\Throwable $previous = null
    ): self {
        return new self($template, sprintf(
            implode(' ', [
                'Could not find the template "%s" for rendering the API response into a web page.',
                'Please try requesting the page again specifying one of the following data encoding formats: %s.',
            ]),
            $template,
            implode(', ', $availableMimeTypes)
        ), $previous, ['template' => $template, 'availableMimeTypes' => $availableMimeTypes]);
    }

    public static function templateNotSpecified(array $availableMimeTypes, ?\Throwable $previous = null): self
    {
        return new self(null, sprintf(
            implode(' ', [
                'A HTML response was requested,',
                'but no template has been defined for rendering the API response into a web page.',
                'Please try requesting the page again specifying one of the following data encoding formats: %s.',
            ]),
            implode(', ', $availableMimeTypes)
        ), $previous, ['availableMimeTypes' => $availableMimeTypes]);
    }

    public static function cannotDecodeHtmlPayload(?\Throwable $previous = null): self
    {
        return new self(null, 'Cannot decode HTML request payloads.');
    }

    public function getAdditionalInformationForConsumer(): array
    {
        return $this->additionalInformation;
    }
}
