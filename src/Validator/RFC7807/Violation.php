<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Validator\RFC7807;

use Darsyn\Unboxer\UnboxableInterface;
use Ramsey\Uuid\Uuid;

class Violation implements ViolationInterface, UnboxableInterface
{
    /** @var string $message */
    private $message;
    /** @var array $parameters */
    private $parameters;
    /** @var string|null $propertyPath */
    private $propertyPath;
    /** @var string|null $code */
    private $code;

    public function __construct(
        string $message,
        ?string $code = null,
        ?string $propertyPath = null,
        array $parameters = []
    ) {
        $this->message = $message;
        $this->parameters = $parameters;
        $this->propertyPath = $propertyPath;
        if (is_string($code) && \preg_match('/' . Uuid::VALID_PATTERN . '/D', $code)) {
            $this->code = \strtolower($code);
        }
    }

    public function getMessage(): string
    {
        return strtr($this->message, $this->getMessageParameters());
    }

    public function getMessageParameters(): array
    {
        return $this->parameters;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getPropertyPath(): ?string
    {
        return $this->propertyPath;
    }

    public function __unbox(bool $root = true): array
    {
        $data = [
            'title' => $this->getMessage(),
            'propertyPath' => $this->getPropertyPath(),
            'type' => $this->code ? 'urn:uuid:' . $this->code : null,
        ];
        $data = $this->appendExtraFieldsToPropertyPath($data);
        return $data;
    }

    /**
     * If a Validation error occurs due to the existence of an additional field, append the name of the erroneous field
     *   to the propertyPath so that the Validation error makes more sense.
     *
     * @param array $data
     * @return array
     */
    private function appendExtraFieldsToPropertyPath(array $data): array
    {
        // if we have extra_fields in the parameters array...
        if (isset($this->parameters['{{ extra_fields }}'])) {
            if (!empty($data['propertyPath'])) {
                // ...append a full-stop if the propertyPath is not root-level...
                $data['propertyPath'] .= '.';
            }
            // ...append the extra_fields value to the propertyPath
            $data['propertyPath']
                .= 'children[' . str_replace(['"', ' '], '', $this->parameters['{{ extra_fields }}']) . ']';
        }
        return $data;
    }
}
