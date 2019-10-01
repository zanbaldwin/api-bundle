<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Response;

use App\Model\LinkableInterface;
use Darsyn\Unboxer\UnboxableInterface;
use Symfony\Component\HttpFoundation\Request;

interface ApiResponseInterface extends LinkableInterface
{
    public function getData(): ?UnboxableInterface;
    public function getStatusCode(?Request $request = null): int;
    public function getHeaders(): array;
}
