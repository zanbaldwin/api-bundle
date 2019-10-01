<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Formatter\Format;

use Intergalactic\ApiBundle\Formatter\AbstractFormatter;

class JSON extends AbstractFormatter
{
    /** {@inheritdoc} */
    public function decode(string $payload)
    {
        $data = \json_decode($payload, true);
        if (json_last_error() !== \JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Error whilst decoding JSON payload.');
        }
        return $data;
    }

    /** {@inheritdoc} */
    public function encode($data): string
    {
        $payload = json_encode($data, JSON_PRESERVE_ZERO_FRACTION);
        if ($payload === false) {
            throw new \InvalidArgumentException('Error whilst encoding data into JSON payload.');
        }
        return $payload;
    }

    /** {@inheritdoc} */
    public function getFormatName(): string
    {
        return 'json';
    }

    /** {@inheritdoc} */
    public function getSupportedMimeTypes(): array
    {
        return [
            'application/json',
            'application/x-json',
            'text/json',
        ];
    }
}
