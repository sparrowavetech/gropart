<?php

namespace Botble\LoyaltyPoints\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\LoyaltyPoints\Plugin;

abstract class BaseLoyaltyController extends BaseController
{
    public function __construct()
    {
        $version = Plugin::ASSETS_VERSION;

        if (BaseHelper::adminLanguageDirection() === 'rtl') {
            Assets::addStylesDirectly("vendor/core/plugins/loyalty-points/css/loyalty-admin-rtl.css?v={$version}");
        } else {
            Assets::addStylesDirectly("vendor/core/plugins/loyalty-points/css/loyalty-admin.css?v={$version}");
        }
    }

    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/loyalty-points::loyalty-points.name'), route('loyalty-points.index'));
    }
}
