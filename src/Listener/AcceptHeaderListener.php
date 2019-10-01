<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Listener;

use Intergalactic\ApiBundle\Exception\NotAcceptableHttpException;
use Intergalactic\ApiBundle\Formatter\CollectionInterface;
use Negotiation\Negotiator;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AcceptHeaderListener implements EventSubscriberInterface
{
    /** @var \App\Formatter\CollectionInterface */
    private $formatters;
    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    public function __construct(CollectionInterface $formatterCollection, LoggerInterface $logger)
    {
        $this->formatters = $formatterCollection;
        $this->logger = $logger;
    }

    public function determineAppropriateRequestFormat(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $this->formatters->registerFormatsWithRequest($request);
        $mimeType = $this->negotiateAcceptableMimeType(
            $request->headers,
            // We must also try to match for HTML types, otherwise default browser Accept headers will always match the
            // first MIME type of the first formatter in the collection.
            array_merge($this->formatters->getSupportedMimeTypes(), $request::getMimeTypes('html'))
        );
        if ($mimeType !== null) {
            $this->logger->debug(sprintf(
                'Successfully negotiated the MIME type "%s" that both the client and application understand.',
                $mimeType
            ), ['mime' => $mimeType]);
            $request->setRequestFormat($request->getFormat($mimeType));
        }
    }

    public function disallowNonRegisteredRequestFormats(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$this->formatters->supportsFormat($request->getRequestFormat())) {
            throw NotAcceptableHttpException::notNegotiable($this->formatters->getSupportedMimeTypes());
        }
    }

    private function negotiateAcceptableMimeType(HeaderBag $requestHeaders, array $acceptableMimeTypes): ?string
    {
        $negotiator = new Negotiator;
        /** @var \Negotiation\Accept $mediaType */
        $mediaType = $negotiator->getBest($requestHeaders->get('Accept'), $acceptableMimeTypes);
        return null !== $mediaType
            ? $mediaType->getType()
            : null;
    }

    /** {@inheritdoc} */
    public static function getSubscribedEvents(): iterable
    {
        yield KernelEvents::REQUEST => ['determineAppropriateRequestFormat', 2];
        // If this was a pure-API project, we'd uncomment the following. But this project also requires that we serve
        // HTML pages, so we can't sent a 406 Not Acceptable response until we know that the controller isn't expecting
        // the result to be appropriately encoded by a listener.
        # yield KernelEvents::REQUEST => ['disallowNonRegisteredRequestFormats', 1];
    }
}
