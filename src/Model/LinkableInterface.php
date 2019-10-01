<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Model;

interface LinkableInterface
{
    /**
     * Returns a list of URLs that the model links to.
     * @return string[]
     */
    public function getLinks(): array;
}
