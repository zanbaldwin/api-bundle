<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Listener;

use Darsyn\Unboxer\UnboxableInterface;
use Darsyn\Unboxer\UnboxerInterface;
use Intergalactic\ApiBundle\Exception\DescriptiveHttpExceptionInterface;
use Intergalactic\ApiBundle\Formatter\CollectionInterface;
use Intergalactic\ApiBundle\Formatter\EncoderInterface;
use Intergalactic\ApiBundle\Formatter\FormatterInterface;
use Intergalactic\ApiBundle\Utility\HTTP;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionListener implements EventSubscriberInterface
{
    /** @var \App\Formatter\FormatterInterface */
    private $formatters;
    /** @var \Darsyn\Unboxer\UnboxerInterface */
    private $unboxer;
    /** @var \Psr\Log\LoggerInterface */
    private $logger;
    /** @var boolean $debug */
    private $debug;

    public function __construct(
        CollectionInterface $formatterCollection,
        UnboxerInterface $unboxer,
        LoggerInterface $logger,
        bool $debug = false
    ) {
        $this->formatters = $formatterCollection;
        $this->unboxer = $unboxer;
        $this->logger = $logger;
        $this->debug = $debug;
    }

    public function encodeExceptionIntoAppropriateRequestFormat(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        try {
            $formatter = $this->formatters->getForFormat($request->getRequestFormat());
        } catch (\InvalidArgumentException $ignore) {
            $this->logger->warning('Unable to determine formatter for request type; cannot encode exception', [
                'request_format' => $request->getRequestFormat(),
            ]);
            // Alternatively, create a anonymous fallback formatter if you don't want Symfony to create a HTML view.
            # $formatter = $this->constructFallbackEncoder();
            return;
        }
        $exception = $event->getException();
        $data = $this->unpackExceptionIntoEncodableStructure($exception);
        $responseBody = $this->encodeDataToResponseBody($formatter, $data);
        $response = $exception instanceof HttpExceptionInterface
            ? new Response($responseBody, $exception->getStatusCode(), $exception->getHeaders())
            : new Response($responseBody, 500);
        HTTP::describeResponseBodyInContentHeaders($response->headers, $responseBody, $request->getRequestFormat());
        $event->setResponse($response);
    }

    private function unpackExceptionIntoEncodableStructure(\Throwable $exception): array
    {
        /** @var array $data */
        $data = $exception instanceof UnboxableInterface
            ? $this->unboxer->unbox($exception)
            : $this->getExceptionDebugInfo($exception);
        if ($exception instanceof DescriptiveHttpExceptionInterface) {
            $data['additionalInfo'] = $exception->getAdditionalInformationForConsumer();
        }
        return $data;
    }

    private function getExceptionDebugInfo(\Throwable $exception): array
    {
        if ($this->debug) {
            return ['message' => $exception->getMessage()];
        }
        $data = [
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'trace' => explode("\n", $exception->getTraceAsString()),
            'on' => [$exception->getFile(), $exception->getLine()],
        ];
        $previous = $exception->getPrevious();
        if ($previous instanceof \Throwable) {
            $data['previous'] = $this->getExceptionDebugInfo($previous);
        }
        return $data;
    }

    private function encodeDataToResponseBody(FormatterInterface $formatter, array $data): string
    {
        try {
            return $formatter->encode($data);
        } catch (\Throwable $throwable) {
            $this->logger->error('Unknown error whilst encoding exception to request format.');
            return 'An unknown error occurred during encoding of exception data.';
        }
    }

    /** @example */
    private function constructFallbackEncoder(): EncoderInterface
    {
        return new class implements EncoderInterface {
            public function encode($data): string
            {
                $data['debug'] = 'Could not find appropriate formatter; defaulting to JSON encoding instead.';
                return json_encode($data);
            }

            public function supportsEncoding(string $format): bool
            {
                return true;
            }
        };
    }

    /** {@inheritdoc} */
    public static function getSubscribedEvents(): iterable
    {
        yield KernelEvents::EXCEPTION => ['encodeExceptionIntoAppropriateRequestFormat', 4];
    }
}
