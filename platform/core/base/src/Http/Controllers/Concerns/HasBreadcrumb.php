<?php

namespace Botble\Base\Http\Controllers\Concerns;

use Botble\Base\Facades\Breadcrumb as BreadcrumbFacade;
use Botble\Base\Supports\Breadcrumb;

trait HasBreadcrumb
{
    protected function breadcrumb(string $group = 'admin'): Breadcrumb
    {
        /**
         * @var Breadcrumb $breadcrumb
         */
        $breadcrumb = BreadcrumbFacade::for($group);

        if ($group === 'admin') {
            $breadcrumb->add(trans('core/dashboard::dashboard.title'), route('dashboard.index'));
        }

        return $breadcrumb;
    }
}
