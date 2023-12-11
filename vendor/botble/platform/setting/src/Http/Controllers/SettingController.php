<?php

namespace Botble\Setting\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\Setting\Http\Controllers\Concerns\InteractsWithSettings;

abstract class SettingController extends BaseController
{
    use InteractsWithSettings;

    protected function breadcrumb(string $group = 'admin'): Breadcrumb
    {
        return parent::breadcrumb($group)
            ->add(trans('core/setting::setting.title'), route('settings.index'));
    }
}
