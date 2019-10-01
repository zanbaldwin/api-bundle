<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Validator\RFC7807;

interface ListInterface extends \ArrayAccess, \SeekableIterator, \Countable
{
    public function add(ViolationInterface $violation): void;
}
