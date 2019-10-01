<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Response;

interface TemplatableResponseInterface extends ApiResponseInterface
{
    public function getTemplateName(): ?string;
}
