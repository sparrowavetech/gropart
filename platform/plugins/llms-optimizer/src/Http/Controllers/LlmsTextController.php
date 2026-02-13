<?php

namespace Shaqi\LlmsOptimizer\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Response;
use Shaqi\LlmsOptimizer\Facades\LlmsGenerator;

class LlmsTextController extends BaseController
{
    public function index(): Response
    {
        if (! llms_optimizer_is_enabled()) {
            abort(404);
        }

        $content = LlmsGenerator::generate();

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}

