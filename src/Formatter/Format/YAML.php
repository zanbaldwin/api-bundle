<?php declare(strict_types=1);

namespace App\Formatter\Format;

use Intergalactic\ApiBundle\Formatter\AbstractFormatter;
use Symfony\Component\Yaml\Exception\DumpException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml as YamlParser;

class YAML extends AbstractFormatter
{
    /** {@inheritdoc} */
    public function decode(string $data)
    {
        try {
            return YamlParser::parse($data, YamlParser::PARSE_DATETIME);
        } catch (ParseException $exception) {
            throw new \InvalidArgumentException('Error whilst decoding YAML payload.', 0, $exception);
        }
    }

    /** {@inheritdoc} */
    public function encode($data): string
    {
        try {
            return YamlParser::dump(
                $data,
                5,
                2,
                YamlParser::DUMP_OBJECT_AS_MAP | YamlParser::DUMP_MULTI_LINE_LITERAL_BLOCK
            );
        } catch (DumpException $exception) {
            throw new \InvalidArgumentException('Error whilst encoding data into YAML payload.', 0, $exception);
        }
    }

    /** {@inheritdoc} */
    public function getFormatName(): string
    {
        return 'yaml';
    }

    /** {@inheritdoc} */
    public function getSupportedMimeTypes(): array
    {
        return [
            'text/yaml',
            'text/yml',
        ];
    }
}
