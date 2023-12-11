<?php

namespace Botble\Installer\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class InstallController extends BaseController
{
    public function index(): View|RedirectResponse
    {
        return view('packages/installer::welcome');
    }
}
