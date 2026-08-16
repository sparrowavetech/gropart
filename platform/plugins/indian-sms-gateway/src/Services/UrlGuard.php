<?php

namespace Ashikul\IndiaSmsGateway\Services;

use InvalidArgumentException;

class UrlGuard
{
    public function assertSafeEndpoint(string $url, bool $allowHttp = false): void
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));

        if (! is_array($parts) || empty($parts['host'])) {
            throw new InvalidArgumentException('The gateway endpoint must be a valid URL.');
        }

        if ($scheme !== 'https' && ! ($allowHttp && $scheme === 'http')) {
            throw new InvalidArgumentException(
                'Use an HTTPS endpoint. Enable legacy HTTP only when the provider does not offer HTTPS.'
            );
        }

        $host = strtolower((string) $parts['host']);

        if ($host === 'localhost' || str_ends_with($host, '.local')) {
            throw new InvalidArgumentException('Local endpoints are not allowed.');
        }

        $addresses = filter_var($host, FILTER_VALIDATE_IP)
            ? [$host]
            : (gethostbynamel($host) ?: []);

        foreach ($addresses as $address) {
            $public = filter_var(
                $address,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );

            if ($public === false) {
                throw new InvalidArgumentException(
                    'Private and reserved gateway addresses are not allowed.'
                );
            }
        }
    }

    public function assertSafeHttps(string $url): void
    {
        $this->assertSafeEndpoint($url, false);
    }
}
