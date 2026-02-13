<?php

namespace Shaqi\LlmsOptimizer\Supports;

class LlmsOptimizerHelper
{
    public function getSettingPrefix(): string
    {
        return llms_optimizer_config('prefix', 'llms_optimizer_');
    }
}

