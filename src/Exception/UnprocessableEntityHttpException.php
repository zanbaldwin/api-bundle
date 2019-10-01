<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Exception;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException as SymfonyUnprocessableEntityHttpException;

class UnprocessableEntityHttpException extends SymfonyUnprocessableEntityHttpException implements
    DescriptiveHttpExceptionInterface
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

    public static function noObjectContext($payload, ?\Throwable $previous = null): self
    {
        $payloadType = gettype($payload);
        return new self(sprintf(
            'Values without context disallowed; request payload must be an object. "%s" supplied.',
            $payloadType
        ), $previous, ['payloadType' => $payloadType]);
    }

    public static function cannotDecode(?\Throwable $previous = null): self
    {
        return new self('Could not decode request payload according to MIME type.', $previous);
    }

    public function getAdditionalInformationForConsumer(): array
    {
        return $this->additionalInformation;
    }
}
