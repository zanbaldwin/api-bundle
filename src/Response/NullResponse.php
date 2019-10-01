<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Response;

class NullResponse extends ModelResponse
{
    public function __construct(array $headers = [])
    {
        parent::__construct(null, null, 204, $headers);
    }
}
