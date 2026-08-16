<?php

namespace Ashikul\IndiaSmsGateway\Services;

class SegmentCounter
{
    public function count(string $message): int
    {
        $unicode = preg_match('/[^\x00-\x7F]/', $message) === 1;
        $length = mb_strlen($message);
        $single = $unicode ? 70 : 160;
        $multipart = $unicode ? 67 : 153;

        return $length <= $single ? 1 : (int) ceil($length / $multipart);
    }

    public function encoding(string $message): string
    {
        return preg_match('/[^\x00-\x7F]/', $message) === 1 ? 'Unicode' : 'GSM-7';
    }
}
