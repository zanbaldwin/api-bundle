<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Exception;

use Darsyn\Unboxer\UnboxableInterface;
use Intergalactic\ApiBundle\Validator\RFC7807;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends BadRequestHttpException implements RFC7807\ErrorInterface, UnboxableInterface
{
    public const NO_DATA_ERROR_CODE = 'df8e21fe-36d7-4b79-a66d-d7f3bda42417';

    /** @var string|null $title */
    private $title;
    /** @var string|null $type */
    private $type;
    /** @var \App\Validator\RFC7807\ListInterface $errors */
    private $violations;
    /** @var string|null $instance */
    private $instance;

    protected function __construct(
        RFC7807\ListInterface $violations,
        ?string $title = null,
        ?string $instance = null,
        ?\Throwable $previous = null,
        array $headers = []
    ) {
        $this->violations = $violations;
        $this->title = $title;
        $this->instance = $instance;
        parent::__construct($this->getTitle(), $previous, 0, $headers);
    }

    public static function fromEmpty(?\Throwable $previous = null, array $headers = []): self
    {
        return new static(new RFC7807\ViolationList([
            new RFC7807\Violation('No data structure supplied', static::NO_DATA_ERROR_CODE, null, []),
        ]), 'No Data', null, $previous, $headers);
    }

    public static function fromForm(FormInterface $form, ?string $instance = null, array $headers = []): self
    {
        if (!$form->isSubmitted()) {
            return static::fromEmpty(null, $headers);
        }
        $constraintViolations = $nonConstraintViolations = [];
        /** @var \Symfony\Component\Form\FormError $error */
        foreach (\iterator_to_array($form->getErrors(true)) as $error) {
            if ($error->getCause() instanceof ConstraintViolationInterface) {
                $constraintViolations[] = $error->getCause();
            } else {
                $nonConstraintViolations[] = $error;
            }
        }
        $violationList = new RFC7807\ViolationList(array_merge(
            array_map(function (ConstraintViolationInterface $error): RFC7807\ViolationInterface {
                return static::constraintViolationToRfcViolation($error);
            }, $constraintViolations),
            array_map(function (FormError $error): RFC7807\ViolationInterface {
                return static::formErrorToRfcViolation($error);
            }, $nonConstraintViolations)
        ));
        return new static($violationList, static::DEFAULT_TITLE, $instance, null, $headers);
    }

    public static function fromConstraintViolationList(
        ConstraintViolationListInterface $list,
        ?string $instance = null,
        ?\Throwable $previous = null,
        array $headers = []
    ): self {
        $violationList = new RFC7807\ViolationList(array_map(function (
            ConstraintViolationInterface $violation
        ): RFC7807\ViolationInterface {
            return static::constraintViolationToRfcViolation($violation);
        }, \iterator_to_array($list)));
        return self::fromRfcViolationList($violationList, $instance, $previous, $headers);
    }

    public static function fromRfcViolationList(
        RFC7807\ListInterface $list,
        ?string $instance = null,
        ?\Throwable $previous = null,
        array $headers = []
    ): self {
        return new static($list, static::DEFAULT_TITLE, $instance, $previous, $headers);
    }

    protected static function constraintViolationToRfcViolation(
        ConstraintViolationInterface $error
    ): RFC7807\ViolationInterface {
        return new RFC7807\Violation(
            $error->getMessage(),
            $error->getCode(),
            $error->getPropertyPath(),
            $error->getParameters()
        );
    }

    protected static function formErrorToRfcViolation(FormError $error): RFC7807\ViolationInterface
    {
        return new RFC7807\Violation($error->getMessage(), null, null, $error->getMessageParameters());
    }

    public function getTitle(): string
    {
        return $this->title ?: static::DEFAULT_TITLE;
    }

    public function getType(): string
    {
        return $this->type ?: static::DEFAULT_TYPE;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getDetail(): string
    {
        return implode("\n", array_map(function (RFC7807\ViolationInterface $violation): string {
            return \is_string($violation->getPropertyPath())
                ? sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage())
                : $violation->getMessage();
        }, iterator_to_array($this->violations)));
    }

    /**
     * Instance defines the fully-qualified ID of the entity that this error is all about. For example: "/users/123".
     * This will most likely be a non-absolute URL generated by the router.
     */
    public function getInstance(): ?string
    {
        return $this->instance;
    }

    public function getViolations(): RFC7807\ListInterface
    {
        return $this->violations;
    }

    public function __unbox(bool $root = true): array
    {
        return array_filter([
            'title' => $this->getTitle(),
            'type' => $this->getType(),
            'detail' => $this->getDetail(),
            'violations' => $this->getViolations(),
            'instance' => $this->getInstance(),
        ], function ($value): bool {
            return $value !== null;
        });
    }
}
