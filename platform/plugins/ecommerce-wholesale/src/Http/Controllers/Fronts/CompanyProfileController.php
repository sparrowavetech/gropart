<?php

namespace Botble\EcommerceWholesale\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\EcommerceWholesale\Http\Requests\UpdateCompanyProfileRequest;
use Botble\EcommerceWholesale\Models\WholesaleApplication;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;

class CompanyProfileController extends BaseController
{
    public function __construct()
    {
        Theme::asset()
            ->add('customer-style', 'vendor/core/plugins/ecommerce/css/customer.css', ['bootstrap-css'], version: EcommerceHelper::getAssetVersion());
    }

    public function edit()
    {
        $customer = auth('customer')->user();

        $application = WholesaleApplication::query()
            ->where('customer_id', $customer->getKey())
            ->latest()
            ->first();

        abort_unless($application, 404);

        SeoHelper::setTitle(trans('plugins/ecommerce-wholesale::wholesale.frontend.edit_company_profile'));

        Theme::breadcrumb()
            ->add(trans('plugins/ecommerce-wholesale::wholesale.frontend.home'), route('public.index'))
            ->add(trans('plugins/ecommerce-wholesale::wholesale.frontend.account'), route('customer.overview'))
            ->add(trans('plugins/ecommerce-wholesale::wholesale.frontend.wholesale_account'), route('customer.wholesale.index'))
            ->add(trans('plugins/ecommerce-wholesale::wholesale.frontend.edit_company_profile'));

        return Theme::scope('wholesale.customer.edit-company-profile', [
            'application' => $application,
            'customer' => $customer,
        ], 'plugins/ecommerce-wholesale::themes.customer.edit-company-profile')->render();
    }

    public function update(UpdateCompanyProfileRequest $request)
    {
        $customer = auth('customer')->user();

        $application = WholesaleApplication::query()
            ->where('customer_id', $customer->getKey())
            ->latest()
            ->first();

        abort_unless($application, 404);

        $application->update($request->validated());

        return $this->httpResponse()
            ->setMessage(trans('plugins/ecommerce-wholesale::wholesale.frontend.company_profile_updated'))
            ->setNextUrl(route('customer.wholesale.index'));
    }
}
