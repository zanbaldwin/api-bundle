<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Validator\RFC7807;

interface ViolationInterface
{
    public const DEFAULT_TYPE = 'https://symfony.com/errors/validation';

    public function getMessage(): string;
    public function getMessageParameters(): array;
    public function getCode(): ?string;
    public function getPropertyPath(): ?string;
}
