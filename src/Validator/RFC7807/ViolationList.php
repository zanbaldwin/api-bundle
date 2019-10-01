<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Validator\RFC7807;

use Darsyn\Unboxer\UnboxableInterface;

class ViolationList implements ListInterface, UnboxableInterface
{
    private $position = 0;
    private $violations = [];

    public function __construct(array $violations = [])
    {
        foreach ($violations as $violation) {
            try {
                $this->add($violation);
            } catch (\TypeError $e) {
                throw $this->createTypeErrorException($violation);
            }
        }
    }

    private function createTypeErrorException($value): \InvalidArgumentException
    {
        return new \InvalidArgumentException('Violation list can only hold violations.', 0, new \TypeError(sprintf(
            'Value passed to offsetSet() must be of type "%s", "%s" given.',
            ViolationInterface::class,
            is_object($value) ? get_class($value) : gettype($value)
        )));
    }

    /** {@inheritdoc} */
    public function current(): ViolationInterface
    {
        if (!$this->valid()) {
            throw new \OutOfBoundsException(sprintf('A violation with key "%s" does not exist.', $this->position));
        }
        return $this->violations[$this->position];
    }

    /** {@inheritdoc} */
    public function next(): void
    {
        ++$this->position;
    }

    /** {@inheritdoc} */
    public function key(): int
    {
        return $this->position;
    }

    /** {@inheritdoc} */
    public function valid(): bool
    {
        return $this->offsetExists($this->position);
    }

    /** {@inheritdoc} */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /** {@inheritdoc} */
    public function offsetExists($offset): bool
    {
        return isset($this->violations[$offset]);
    }

    /** {@inheritdoc} */
    public function offsetGet($offset): ViolationInterface
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfBoundsException(sprintf('A violation with key "%s" does not exist.', $offset));
        }
        return $this->violations[$offset];
    }

    /** {@inheritdoc} */
    public function offsetSet($offset, $value): void
    {
        if (!$value instanceof ViolationInterface) {
            $this->createTypeErrorException($value);
        }
        $this->violations[] = $value;
    }

    /** {@inheritdoc} */
    public function offsetUnset($offset): void
    {
        if ($this->offsetExists($offset) && $offset <= $this->position) {
            unset($this->violations[$offset]);
            $this->violations = \array_values($this->violations);
            --$this->position;
        }
    }

    /** {@inheritdoc} */
    public function count(): int
    {
        return \count($this->violations);
    }

    public function add(ViolationInterface $violation): void
    {
        $this->violations[] = $violation;
    }

    /** {@inheritdoc} */
    public function seek($position): void
    {
        if (!$this->offsetExists($position)) {
            throw new \OutOfBoundsException(sprintf('A violation with key "%s" does not exist.', $position));
        }
        $this->position = $position;
    }

    public function __unbox(bool $root = true): array
    {
        return $this->violations;
    }
}
