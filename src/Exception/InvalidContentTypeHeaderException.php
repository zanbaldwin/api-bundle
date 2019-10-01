<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Exception;

use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

class InvalidContentTypeHeaderException extends UnsupportedMediaTypeHttpException implements
    DescriptiveHttpExceptionInterface
{
    /** @var array<string> */
    private $additionalInformation;

    public function __construct(
        string $message = '',
        ?\Throwable $previous = null,
        array $additionalInformation = [],
        array $headers = []
    ) {
        parent::__construct($message, $previous, 0, $headers);
        $this->additionalInformation = $additionalInformation;
    }

    public static function missingHeader(array $supportedMimeTypes = [], ?\Throwable $previous = null): self
    {
        return new self('Content-Type header not supplied; unable to decode payload.', $previous, [
            'supportedMimeTypes' => $supportedMimeTypes,
        ]);
    }

    public static function notSupported(
        string $suppliedContentType,
        array $supportedMimeTypes = [],
        ?\Throwable $previous = null
    ): self {
        return new self(sprintf(
            'Request content type "%s" is not supported; unable to decode payload.',
            $suppliedContentType
        ), $previous, ['supportedMimeTypes' => $supportedMimeTypes]);
    }

    /** {@inheritdoc} */
    public function getAdditionalInformationForConsumer(): array
    {
        return $this->additionalInformation;
    }
}
