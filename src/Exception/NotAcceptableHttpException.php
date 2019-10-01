<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Exception;

use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException as SymfonyNotAcceptableHttpException;

class NotAcceptableHttpException extends SymfonyNotAcceptableHttpException implements DescriptiveHttpExceptionInterface
{
    /** @var array */
    private $additionalInformation;

    public function __construct(
        string $message,
        ?\Throwable $previous = null,
        array $additionalInformation = [],
        array $headers = []
    ) {
        parent::__construct($message, $previous, 0, $headers);
        $this->additionalInformation = $additionalInformation;
    }

    public static function notNegotiable(array $availableMimeTypes, ?\Throwable $previous = null): self
    {
        throw new self(sprintf(
            'Could not negotiate an acceptable encoding method; available MIME types are: %s.',
            implode(', ', $availableMimeTypes)
        ), $previous, ['availableMimeTypes' => $availableMimeTypes]);
    }

    public function getAdditionalInformationForConsumer(): array
    {
        return $this->additionalInformation;
    }
}
