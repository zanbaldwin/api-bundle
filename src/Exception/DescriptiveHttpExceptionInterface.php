<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

interface DescriptiveHttpExceptionInterface extends HttpExceptionInterface
{
    public function getAdditionalInformationForConsumer(): array;
}
