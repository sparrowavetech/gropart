<?php

namespace Botble\EcommerceWholesale\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Botble\EcommerceWholesale\Forms\WholesaleSettingForm;
use Botble\EcommerceWholesale\Http\Requests\WholesaleSettingRequest;
use Botble\Setting\Http\Controllers\SettingController;

class WholesaleSettingController extends SettingController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/ecommerce-wholesale::wholesale.name'), route('wholesale.customer-groups.index'))
            ->add(trans('plugins/ecommerce-wholesale::wholesale.settings'));
    }

    public function edit()
    {
        $this->pageTitle(trans('plugins/ecommerce-wholesale::wholesale.settings'));

        return WholesaleSettingForm::create()->renderForm();
    }

    public function update(WholesaleSettingRequest $request): BaseHttpResponse
    {
        return $this->performUpdate($request->validated());
    }
}
