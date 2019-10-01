<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Listener;

use Intergalactic\ApiBundle\Exception\InvalidContentTypeHeaderException;
use Intergalactic\ApiBundle\Exception\UnprocessableEntityHttpException;
use Intergalactic\ApiBundle\Formatter\CollectionInterface;
use Intergalactic\ApiBundle\Formatter\FormatterInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestPayloadListener implements EventSubscriberInterface
{
    public const PAYLOAD_REQUIRED_FOR_METHODS = ['POST', 'PUT', 'PATCH'];

    /** @var \App\Formatter\CollectionInterface */
    private $formatters;
    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    public function __construct(CollectionInterface $formatterCollection, LoggerInterface $logger)
    {
        $this->formatters = $formatterCollection;
        $this->logger = $logger;
    }

    public function decodeRequestBodyIntoAttributes(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$this->isRequestBodyDecodable($request)) {
            return;
        }
        $this->logger->debug('Request body supplied, decoding into payload.');
        $formatter = $this->getRequestPayloadFormatter($request->headers);
        try {
            if (!is_array($payload = $formatter->decode($request->getContent()))) {
                throw UnprocessableEntityHttpException::noObjectContext($payload);
            }
        } catch (\InvalidArgumentException $exception) {
            $this->logger->notice('Request body cannot be decoded by formatter according to content type.');
            throw UnprocessableEntityHttpException::cannotDecode($exception);
        }
        $request->request->replace($payload);
    }

    private function isRequestBodyDecodable(Request $request): bool
    {
            // Has request body already been decoded automatically by PHP ("application/x-www-form-urlencoded").
        return $request->request->count() === 0
            // Has request body already been decoded automatically by PHP ("multipart/form-data").
            && $request->files->count() === 0
            // Only bother decoding the request body for HTTP methods that allow a request body according to the spec.
            && in_array(strtoupper($request->getMethod()), static::PAYLOAD_REQUIRED_FOR_METHODS, true)
            // Only bother checking for fatal behaviour if the end-client actually sent something in the request body.
            && !empty($requestBody = trim($request->getContent()));
    }

    private function getRequestPayloadFormatter(HeaderBag $requestHeaders): FormatterInterface
    {
        if (!$requestHeaders->has('Content-Type')) {
            throw InvalidContentTypeHeaderException::missingHeader($this->formatters->getSupportedMimeTypes());
        }
        $contentType = trim(explode(';', $requestHeaders->get('Content-Type'), 2)[0]);
        try {
            return $this->formatters->getForMimeType($contentType);
        } catch (\InvalidArgumentException $exception) {
            throw InvalidContentTypeHeaderException::notSupported(
                $contentType,
                $this->formatters->getSupportedMimeTypes(),
                $exception
            );
        }
    }

    /** {@inheritdoc} */
    public static function getSubscribedEvents(): iterable
    {
        yield KernelEvents::REQUEST => ['decodeRequestBodyIntoAttributes', 0];
    }
}
