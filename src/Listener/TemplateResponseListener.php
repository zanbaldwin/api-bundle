<?php declare(strict_types=1);

namespace App\Listener;

use App\Formatter\CollectionInterface;
use App\Formatter\FormatterCollection;
use App\Response\TemplateResponseInterface;
use App\Utility\HTTP;
use Intergalactic\ApiBundle\Exception\RenderException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TemplateResponseListener implements EventSubscriberInterface
{
    /** @var \Twig\Environment */
    private $twig;
    /** @var \App\Formatter\CollectionInterface */
    private $formatters;

    public function __construct(TwigEnvironment $twig, CollectionInterface $formatters)
    {
        $this->twig = $twig;
        $this->formatters = $formatters;
    }

    public function renderTemplateResponse(ViewEvent $event): void
    {
        $controllerResult = $event->getControllerResult();
        if ($event->hasResponse() || !$controllerResult instanceof TemplateResponseInterface) {
            return;
        }
        if (empty($controllerResult->getTemplateName())) {
            throw RenderException::templateNotSpecified($this->formatters->getSupportedMimeTypes());
        }
        try {
            $response = new Response(
                $content = $this->twig->render(
                    $controllerResult->getTemplateName(),
                    $controllerResult->getParameters()
                ),
                $controllerResult->getStatusCode(),
                $controllerResult->getHeaders()
            );
        } catch (LoaderError $exception) {
            throw RenderException::nonExistentTemplate(
                $controllerResult->getTemplateName(),
                $this->formatters->getSupportedMimeTypes()
            );
        }
        HTTP::describeResponseBodyInContentHeaders($response->headers, $content, 'html');
        $event->setResponse($response);
    }

    /** {@inheritdoc} */
    public static function getSubscribedEvents(): iterable
    {
        yield KernelEvents::VIEW => ['renderTemplateResponse'];
    }
}
