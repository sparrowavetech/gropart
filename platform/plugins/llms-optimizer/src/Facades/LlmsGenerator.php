<?php

namespace Shaqi\LlmsOptimizer\Facades;

use Illuminate\Support\Facades\Facade;
use Shaqi\LlmsOptimizer\Services\LlmsGeneratorService;

/**
 * @method static string generate()
 * @method static array getContentTypes()
 * @method static string formatSection(string $type, array $items)
 * @method static string buildOptionalSection()
 * @method static int estimateTokenCount(string $content)
 * @method static bool saveStaticFile()
 * @method static void clearCache()
 *
 * @see \Shaqi\LlmsOptimizer\Services\LlmsGeneratorService
 */
class LlmsGenerator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LlmsGeneratorService::class;
    }
}

