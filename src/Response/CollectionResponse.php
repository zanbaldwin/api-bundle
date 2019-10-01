<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Response;

use Darsyn\Unboxer\UnboxableInterface;

class RawArrayResponse extends ModelResponse
{
    public function __construct(array $data, ?string $template, ?int $statusCode = null, array $headers = [])
    {
        parent::__construct(new class($data) implements UnboxableInterface
        {
            private $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function __unbox(): array
            {
                return $this->data;
            }
        }, $template, $statusCode, $headers);
    }
}
