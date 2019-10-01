<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Validator\RFC7807;

interface ErrorInterface
{
    public const DEFAULT_TITLE = 'Validation Failed';
    public const DEFAULT_TYPE = 'https://symfony.com/errors/validation';

    public function getTitle(): string;
    public function getType(): string;
    public function getDetail(): string;
    public function getInstance(): ?string;
    public function getViolations(): ListInterface;
}
