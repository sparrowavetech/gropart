<?php

namespace Botble\Base\Http\Controllers;

class BaseSystemController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans('core/base::base.panel.system'), route('system.index'));
    }
}
