<?php

namespace SparroWave\Shipmozo\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ShipmozoLogController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/ecommerce::ecommerce.name'))
            ->add(trans('plugins/ecommerce::shipping.shipping_methods'), route('ecommerce.settings.shipping'))
            ->add(trans('plugins/shipmozo::shipmozo.logs.page_title'), route('ecommerce.shipments.shipmozo.logs.index'));
    }

    public function index(Request $request)
    {
        $this->pageTitle(trans('plugins/shipmozo::shipmozo.logs.page_title'));

        $this->pruneLogs();

        $logs = [];
        $logDir = storage_path('logs');

        if (File::isDirectory($logDir)) {
            $files = BaseHelper::scanFolder($logDir);
            foreach ($files as $file) {
                if ($file === 'shipmozo.log' || (Str::startsWith($file, 'shipmozo-') && Str::endsWith($file, '.log'))) {
                    $filePath = $logDir . '/' . $file;
                    $size = File::size($filePath);
                    $mtime = File::lastModified($filePath);

                    $logs[] = [
                        'file' => $file,
                        'size' => $this->formatBytes($size),
                        'raw_size' => $size,
                        'updated_at' => Carbon::createFromTimestamp($mtime),
                        'created_at' => $this->resolveFileDate($file, $mtime),
                    ];
                }
            }
        }

        // Sort descending by updated_at
        usort($logs, fn ($a, $b) => $b['updated_at']->timestamp <=> $a['updated_at']->timestamp);

        return view('plugins/shipmozo::logs.index', compact('logs'));
    }

    public function show(string $file, Request $request)
    {
        abort_unless(
            preg_match('/\Ashipmozo(?:-\d{4}-\d{2}-\d{2})?\.log\z/', $file) === 1,
            404
        );

        $filePath = storage_path('logs/' . basename($file));
        abort_unless(File::isFile($filePath), 404);

        $this->pageTitle(trans('plugins/shipmozo::shipmozo.logs.view_title', ['file' => $file]));

        $this->breadcrumb()->add($file, route('ecommerce.shipments.shipmozo.logs.show', $file));

        $content = File::get($filePath);
        $entries = $this->parseLogContent($content);

        // Optional filtering by level
        $levelFilter = strtoupper((string) $request->input('level', 'ALL'));
        if ($levelFilter !== 'ALL') {
            $entries = array_filter($entries, fn ($e) => ($e['level'] ?? '') === $levelFilter);
        }

        $fileSize = $this->formatBytes(File::size($filePath));
        $fileModified = Carbon::createFromTimestamp(File::lastModified($filePath));

        return view('plugins/shipmozo::logs.show', compact('file', 'entries', 'fileSize', 'fileModified', 'levelFilter'));
    }

    public function download(string $file)
    {
        abort_unless(
            preg_match('/\Ashipmozo(?:-\d{4}-\d{2}-\d{2})?\.log\z/', $file) === 1,
            404
        );

        $filePath = storage_path('logs/' . basename($file));
        abort_unless(File::isFile($filePath), 404);

        return response()->download($filePath, $file, [
            'Content-Type' => 'text/plain',
        ]);
    }

    public function destroy(string $file, BaseHttpResponse $response)
    {
        abort_unless(
            preg_match('/\Ashipmozo(?:-\d{4}-\d{2}-\d{2})?\.log\z/', $file) === 1,
            404
        );

        $filePath = storage_path('logs/' . basename($file));
        abort_unless(File::isFile($filePath), 404);

        File::delete($filePath);

        return $response
            ->setNextUrl(route('ecommerce.shipments.shipmozo.logs.index'))
            ->setMessage(trans('plugins/shipmozo::shipmozo.logs.deleted_success'));
    }

    protected function pruneLogs(): void
    {
        $logDir = storage_path('logs');
        if (! File::isDirectory($logDir)) {
            return;
        }

        $cutoff = Carbon::now()->subDays(30)->startOfDay()->timestamp;
        $files = BaseHelper::scanFolder($logDir);

        foreach ($files as $file) {
            if (Str::startsWith($file, 'shipmozo-') && Str::endsWith($file, '.log')) {
                $filePath = $logDir . '/' . $file;
                $fileDate = $this->resolveFileDate($file, File::lastModified($filePath));

                if ($fileDate->timestamp < $cutoff) {
                    File::delete($filePath);
                }
            }
        }
    }

    protected function parseLogContent(string $content): array
    {
        if (trim($content) === '') {
            return [];
        }

        // Split log content into blocks by start-of-entry timestamp header
        $rawBlocks = preg_split('/(?=\[\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}\])/', $content, -1, PREG_SPLIT_NO_EMPTY);

        $entries = [];
        $headerPattern = '/^\[(?<date>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\]\s+[a-zA-Z0-9_\-]+\.(?<level>[A-Z]+):\s*(?<body>.*)$/s';

        foreach ($rawBlocks as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }

            if (preg_match($headerPattern, $block, $matches)) {
                $dateStr = $matches['date'];
                $level = strtoupper(trim($matches['level']));
                $body = trim($matches['body']);

                $firstNewline = strpos($body, "\n");
                $firstLine = $firstNewline !== false ? trim(substr($body, 0, $firstNewline)) : $body;
                $extraLines = $firstNewline !== false ? trim(substr($body, $firstNewline + 1)) : '';

                $message = $firstLine;
                $formattedContext = null;

                // Match trailing JSON { ... } or [ ... ] on the first line
                if (preg_match('/^(?<msg>.*?)\s*(?<json>\{.*\}|\[.*\])\s*$/s', $firstLine, $jsonMatches)) {
                    $jsonCandidate = trim($jsonMatches['json']);
                    $decoded = json_decode($jsonCandidate, true);
                    if (json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded))) {
                        $msg = trim($jsonMatches['msg']);
                        $message = $msg !== '' ? $msg : trans('plugins/shipmozo::shipmozo.logs.payload');
                        $formattedContext = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    }
                }

                // If not found on first line, check if entire body is JSON
                if ($formattedContext === null && (Str::startsWith($body, '{') || Str::startsWith($body, '['))) {
                    $decoded = json_decode($body, true);
                    if (json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded))) {
                        $message = trans('plugins/shipmozo::shipmozo.logs.payload');
                        $formattedContext = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                        $extraLines = '';
                    }
                }

                if ($extraLines !== '') {
                    $formattedContext = ($formattedContext ? $formattedContext . "\n\n--- Details / Stack Trace ---\n" : '') . $extraLines;
                }

                try {
                    $parsedDate = Carbon::parse($dateStr);
                } catch (\Throwable) {
                    $parsedDate = Carbon::now();
                }

                $entries[] = [
                    'date' => $parsedDate,
                    'level' => $level,
                    'message' => $message !== '' ? $message : $level . ' Log Entry',
                    'context' => $formattedContext,
                ];
            } else {
                $entries[] = [
                    'date' => Carbon::now(),
                    'level' => 'INFO',
                    'message' => $block,
                    'context' => null,
                ];
            }
        }

        // Return latest entries first, capping at 500 for optimal browser performance
        $entries = array_reverse($entries);
        if (count($entries) > 500) {
            $entries = array_slice($entries, 0, 500);
        }

        return $entries;
    }

    protected function resolveFileDate(string $filename, int $mtime): Carbon
    {
        if (preg_match('/shipmozo-(\d{4}-\d{2}-\d{2})\.log/', $filename, $matches)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $matches[1])->startOfDay();
            } catch (\Throwable) {
            }
        }

        return Carbon::createFromTimestamp($mtime);
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
