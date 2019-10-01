<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Response;

use App\Model\LinkableInterface;
use Darsyn\Unboxer\UnboxableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ModelResponse implements TemplatableResponseInterface
{
    /** @var \Darsyn\Unboxer\UnboxableInterface|null $data */
    private $data;
    /** @var string|null */
    protected $template;
    /** @var integer|null $statusCode */
    private $statusCode;
    /** @var array $headers */
    private $headers;

    public function __construct(
        ?UnboxableInterface $data,
        ?string $template,
        ?int $statusCode = null,
        array $headers = []
    ) {
        $this->data = $data;
        $this->template = $template;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function getData(): ?UnboxableInterface
    {
        return $this->data;
    }

    public function getTemplateName(): ?string
    {
        return $this->template;
    }

    public function getStatusCode(?Request $request = null): int
    {
        if (\is_int($this->statusCode)) {
            return $this->statusCode;
        }
        if ($this->data === null) {
            return SymfonyResponse::HTTP_NO_CONTENT;
        }
        if ($request instanceof Request && $request->getMethod() === 'POST') {
            return SymfonyResponse::HTTP_CREATED;
        }
        return SymfonyResponse::HTTP_OK;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getLinks(): array
    {
        return $this->data instanceof LinkableInterface
            ? $this->data->getLinks()
            : [];
    }
}
