<?php

namespace Shaqi\LlmsOptimizer\Facades;

use Illuminate\Support\Facades\Facade;
use Shaqi\LlmsOptimizer\Supports\LlmsOptimizerHelper as LlmsOptimizerHelperService;

/**
 * @method static string getSettingPrefix()
 *
 * @see \Shaqi\LlmsOptimizer\Supports\LlmsOptimizerHelper
 */
class LlmsOptimizerHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LlmsOptimizerHelperService::class;
    }
}

