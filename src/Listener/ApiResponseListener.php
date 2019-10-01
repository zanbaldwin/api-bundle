<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Listener;

use Darsyn\Unboxer\UnboxerInterface;
use Intergalactic\ApiBundle\Exception\NotAcceptableHttpException;
use Intergalactic\ApiBundle\Formatter\CollectionInterface;
use Intergalactic\ApiBundle\Formatter\FormatterInterface;
use Intergalactic\ApiBundle\Model\LinkableInterface;
use Intergalactic\ApiBundle\Response\ApiResponseInterface;
use Intergalactic\ApiBundle\Service\LinkManager;
use Intergalactic\ApiBundle\Utility\HTTP;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiResponseListener implements EventSubscriberInterface
{
    /** @var \App\Formatter\CollectionInterface */
    private $formatters;
    /** @var \Darsyn\Unboxer\UnboxerInterface */
    private $unboxer;
    /** @var \App\Service\LinkManager */
    private $linkManager;

    public function __construct(
        CollectionInterface $formatterCollection,
        UnboxerInterface $unboxer,
        LinkManager $linkManager
    ) {
        $this->formatters = $formatterCollection;
        $this->unboxer = $unboxer;
        $this->linkManager = $linkManager;
    }

    public function encodeApiResponseToAppropriateRequestFormat(ViewEvent $event): void
    {
        $controllerResult = $event->getControllerResult();
        if ($event->hasResponse() || !$controllerResult instanceof ApiResponseInterface) {
            return;
        }
        $request = $event->getRequest();
        $formatter = $this->getRequestFormatter($request->getRequestFormat());
        $payload = $this->unboxer->unbox($controllerResult->getData());
        $response = new Response(
            // Don't catch exceptions thrown here: will be caught by ExceptionListener and converted to 500's.
            $responseBody = null !== $payload ? $formatter->encode($payload) : null,
            $controllerResult->getStatusCode($request),
            $controllerResult->getHeaders()
        );
        HTTP::describeResponseBodyInContentHeaders($response->headers, $responseBody, $request->getRequestFormat());
        $this->addLinks($request, $controllerResult);
        $event->setResponse($response);
    }

    private function getRequestFormatter(string $format): FormatterInterface
    {
        try {
            return $this->formatters->getForFormat($format);
        } catch (\InvalidArgumentException $exception) {
            throw NotAcceptableHttpException::notNegotiable($this->formatters->getSupportedMimeTypes(), $exception);
        }
    }

    private function addLinks(Request $request, LinkableInterface $data): void
    {
        $requestAttributes = $request->attributes;
        $linkProvider = $this->linkManager->addLinks(
            $data->getlinks(),
            $requestAttributes->get('_links')
        );
        // To be attached to the response by a first-party Symfony listener.
        $requestAttributes->set('_links', $linkProvider);
    }

    /** {@inheritdoc} */
    public static function getSubscribedEvents(): iterable
    {
        yield KernelEvents::VIEW => ['encodeApiResponseToAppropriateRequestFormat', 64];
    }
}
