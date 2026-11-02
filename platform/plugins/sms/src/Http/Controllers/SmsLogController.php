<?php

namespace Botble\Sms\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Carbon\Carbon;

class SmsLogController extends BaseController
{
    private const FILE_PATTERN = '/\Asms-\d{4}-\d{2}-\d{2}\.log\z/';
    private const MAX_READ_BYTES = 2097152;
    private const MAX_ENTRIES = 300;

    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/sms::sms.title'), route('sms.index'))
            ->add(trans('plugins/sms::sms.logs.title'), route('sms.logs.index'));
    }

    public function index()
    {
        $this->pageTitle(trans('plugins/sms::sms.logs.title'));

        $logs = [];
        foreach (glob(storage_path('logs/sms-*.log')) ?: [] as $path) {
            $file = basename($path);
            if (preg_match(self::FILE_PATTERN, $file) !== 1 || is_link($path) || ! is_file($path)) {
                continue;
            }

            $logs[] = [
                'file' => $file,
                'size' => $this->formatBytes((int) filesize($path)),
                'modified' => Carbon::createFromTimestamp((int) filemtime($path)),
            ];
        }

        usort($logs, fn (array $a, array $b): int => strcmp($b['file'], $a['file']));

        return view('plugins/sms::logs.index', compact('logs'));
    }

    public function show(string $file)
    {
        abort_unless(preg_match(self::FILE_PATTERN, $file) === 1, 404);

        $directory = realpath(storage_path('logs'));
        $path = storage_path('logs/' . $file);
        abort_unless(
            $directory !== false
            && ! is_link($path)
            && is_file($path)
            && is_readable($path)
            && dirname((string) realpath($path)) === $directory,
            404
        );

        $this->pageTitle(trans('plugins/sms::sms.logs.view_title', ['file' => $file]));
        $this->breadcrumb()->add($file, route('sms.logs.show', $file));

        $size = (int) filesize($path);
        $handle = fopen($path, 'rb');
        abort_unless($handle !== false, 404);
        try {
            if ($size > self::MAX_READ_BYTES) {
                fseek($handle, -self::MAX_READ_BYTES, SEEK_END);
            }
            $content = stream_get_contents($handle);
        } finally {
            fclose($handle);
        }

        $entries = $this->parseEntries((string) $content);
        $fileSize = $this->formatBytes($size);
        $fileModified = Carbon::createFromTimestamp((int) filemtime($path));
        $truncated = $size > self::MAX_READ_BYTES || count($entries) >= self::MAX_ENTRIES;

        return view('plugins/sms::logs.show', compact('file', 'entries', 'fileSize', 'fileModified', 'truncated'));
    }

    public function parseEntries(string $content): array
    {
        $blocks = preg_split('/(?=^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] )/m', $content, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $entries = [];

        foreach (array_slice($blocks, -self::MAX_ENTRIES) as $block) {
            if (preg_match('/^\[(?<date>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] [\w-]+\.(?<level>[A-Z]+): (?<body>.*)$/s', trim($block), $match) !== 1) {
                continue;
            }

            $body = trim($match['body']);
            $lineEnd = strpos($body, "\n");
            $firstLine = $lineEnd === false ? $body : substr($body, 0, $lineEnd);
            $context = $lineEnd === false ? '' : trim(substr($body, $lineEnd + 1));
            $message = $firstLine;

            if (preg_match('/^(.*?)\s+(\{.*\})$/s', $firstLine, $json) === 1) {
                $decoded = json_decode($json[2], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $message = trim($json[1]);
                    $context = trim((string) json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))
                        . ($context !== '' ? "\n\n" . $context : '');
                }
            }

            $entries[] = [
                'date' => $match['date'],
                'level' => $match['level'],
                'message' => $message,
                'context' => $context,
            ];
        }

        return array_reverse($entries);
    }

    private function formatBytes(int $size): string
    {
        if ($size < 1024) {
            return $size . ' B';
        }

        if ($size < 1048576) {
            return round($size / 1024, 2) . ' KB';
        }

        return round($size / 1024 / 1024, 2) . ' MB';
    }
}
