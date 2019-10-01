<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Utility;

use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;

class HTTP
{
    public static function describeResponseBodyInContentHeaders(
        HeaderBag $responseHeaders,
        ?string $payload,
        string $requestFormat
    ): void {
        if ($payload !== null) {
            $contentType = static::getContentTypeWithCharset($requestFormat);
            $responseHeaders->add([
                // The controller cannot know the response length since it does not deal with API encoding.
                // Content-Length header is the length in octets (8-bit bytes) NOT character count (RFC 7230, § 3.3.2).
                'Content-Length' => Binary::getLength($payload),
                // Technically this is wrong. Since request formats can relate to multiple MIME types, the MIME type
                // returned here may be different to the MIME type requested by the end-client even if they resolve to
                // the same request format. But that means transferring values between AcceptHeaderListener and here
                // so we'll settle for "good enough for now".
                'Content-Type' => $contentType,
            ]);
        }
    }

    private static function getContentTypeWithCharset(string $requestFormat): string
    {
        $contentType = Request::getMimeTypes($requestFormat)[0] ?? 'application/octet-stream';
        if (false !== $pos = strpos($contentType, ';')) {
            $contentType = substr($contentType, 0, $pos);
        }
        return $contentType . '; charset=utf-8';
    }
}
