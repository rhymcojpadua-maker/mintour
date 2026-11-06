<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaUrl
{
    public static function resolve(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            $url = parse_url($path);
            $host = strtolower($url['host'] ?? '');

            if (in_array($host, ['localhost', '127.0.0.1', '::1'], true) || Str::endsWith($host, '.test')) {
                return ($url['path'] ?? '/')
                    . (isset($url['query']) ? '?'.$url['query'] : '')
                    . (isset($url['fragment']) ? '#'.$url['fragment'] : '');
            }

            return $path;
        }

        if (Str::startsWith($path, '/')) {
            return $path;
        }

        return Storage::url($path);
    }
}