<?php

namespace Botble\MobileCommandBar\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MobileCommandBarAssetController
{
    protected array $mimeTypes = [
        'css' => 'text/css; charset=UTF-8',
        'js' => 'application/javascript; charset=UTF-8',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp',
    ];

    public function show(Request $request, string $path = ''): Response|BinaryFileResponse
    {
        $path = (string) $request->route('path', $path);

        // Reject any attempt to escape the plugin's public/ directory.
        if ($path === '' || str_contains($path, '..') || str_starts_with($path, '/')) {
            abort(404);
        }

        $base = realpath(dirname(__DIR__, 3) . '/public');
        $full = $base ? realpath($base . '/' . $path) : false;

        if (! $base || ! $full || ! str_starts_with($full, $base) || ! is_file($full)) {
            abort(404);
        }

        $extension = strtolower(pathinfo($full, PATHINFO_EXTENSION));
        $mime = $this->mimeTypes[$extension] ?? 'application/octet-stream';

        return response()->file($full, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
