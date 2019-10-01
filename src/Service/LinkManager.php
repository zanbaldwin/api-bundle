<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Service;

use Fig\Link\GenericLinkProvider;
use Fig\Link\Link;
use Intergalactic\ApiBundle\Model\LinkableInterface;
use Psr\Link\EvolvableLinkProviderInterface as LinkProviderInterface;
use Symfony\Component\Routing\Exception\ExceptionInterface as GeneratorException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LinkManager
{
    /** @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface */
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function addLinks(array $links, ?LinkProviderInterface $linkProvider = null): LinkProviderInterface
    {
        $linkProvider = $linkProvider ?? new GenericLinkProvider;
        foreach ($links as $link) {
            $link = $this->evaluateLink($link);
            $linkProvider = $linkProvider->withLink(new Link('preload', $link));
        }
        return $linkProvider;
    }

    private function evaluateLink($link): string
    {
        if (is_string($link)) {
            return $link;
        }
        if (is_array($link) && isset($link[0]) && is_string($link[0])) {
            try {
                return $this->urlGenerator->generate($link[0], $link[1] ?? []);
            } catch (GeneratorException $exception) {
                throw new \InvalidArgumentException(sprintf('Could not generate link from route name "%s".', $link[0]));
            }
        }
        throw new \TypeError(sprintf(
            'Argument returned from %s::getLinks() must be of type string or array, "%s" given.',
            LinkableInterface::class,
            is_object($link) ? get_class($link) : gettype($link)
        ));
    }
}
