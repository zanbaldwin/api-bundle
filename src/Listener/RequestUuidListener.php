<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Listener;

use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestUuidListener implements EventSubscriberInterface
{
    public const REQUEST_ID_ATTRIBUTE = 'request_id';
    public const REQUEST_ID_HEADER = 'X-Request-Id';

    /** @var string */
    private $headerName;
    /** @var string */
    private $requestUuid;

    public function __construct(?string $nodeIdentifier = null, string $header = self::REQUEST_ID_HEADER)
    {
        $this->headerName = $header;
        $this->requestUuid = (string) Uuid::uuid1($nodeIdentifier);
    }

    public function generateRequestUuid(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if ($request instanceof Request && ($requestAttributes = $request->attributes) instanceof ParameterBag) {
            $requestAttributes->set(
                static::REQUEST_ID_ATTRIBUTE,
                $this->requestUuid
            );
        }
    }

    public function injectRequestUuidAsResponseHeader(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        if ($response instanceof Response && ($requestHeaders = $response->headers) instanceof ResponseHeaderBag) {
            $requestHeaders->set(
                $this->headerName,
                (string) $this->requestUuid
            );
        }
    }

    /** Invokable Monolog Processor*/
    public function __invoke(array $record): array
    {
        if (!isset($record['extra']) || !is_array($record['extra'])) {
            $record['extra'] = [];
        }
        $record['extra'][static::REQUEST_ID_ATTRIBUTE] = (string) $this->requestUuid;
        return $record;
    }

    /** {@inheritdoc} */
    public static function getSubscribedEvents(): iterable
    {
        // needs to be one of the first listeners to fire so that the request ID is always available in logging.
        yield KernelEvents::REQUEST => ['generateRequestUuid', 512];
        // Needs to get fired *after* ExceptionListener::encodeExceptionIntoAppropriateRequestFormat() has generated a
        // response, but useful if it's fired before the Symfony profiler so that the request UUID is added to the logs.
        yield KernelEvents::RESPONSE => ['injectRequestUuidAsResponseHeader', -127];
    }
}
