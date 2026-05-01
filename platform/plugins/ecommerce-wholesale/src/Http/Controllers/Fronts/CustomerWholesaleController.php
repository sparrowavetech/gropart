<?php

namespace Botble\EcommerceWholesale\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\EcommerceWholesale\Enums\ApplicationStatusEnum;
use Botble\EcommerceWholesale\Facades\WholesaleHelper;
use Botble\EcommerceWholesale\Http\Requests\ReapplyWholesaleRequest;
use Botble\EcommerceWholesale\Models\CustomerGroupAssignment;
use Botble\EcommerceWholesale\Models\WholesaleApplication;
use Botble\EcommerceWholesale\Services\ReapplicationService;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;

class CustomerWholesaleController extends BaseController
{
    public function __construct()
    {
        Theme::asset()
            ->add('customer-style', 'vendor/core/plugins/ecommerce/css/customer.css', ['bootstrap-css'], version: EcommerceHelper::getAssetVersion());
    }

    public function index()
    {
        $customer = auth('customer')->user();

        $application = WholesaleApplication::query()
            ->where('customer_id', $customer->getKey())
            ->with(['assignedGroup', 'reviewer'])
            ->latest()
            ->first();

        if (! $application) {
            return redirect()->route('public.wholesale.register');
        }

        SeoHelper::setTitle(trans('plugins/ecommerce-wholesale::wholesale.frontend.wholesale_account'));

        Theme::breadcrumb()
            ->add(trans('plugins/ecommerce-wholesale::wholesale.frontend.home'), route('public.index'))
            ->add(trans('plugins/ecommerce-wholesale::wholesale.frontend.account'), route('customer.overview'))
            ->add(trans('plugins/ecommerce-wholesale::wholesale.frontend.wholesale_account'));

        $groupAssignment = null;

        if ($application->status->getValue() === ApplicationStatusEnum::APPROVED && $application->assigned_group_id) {
            $groupAssignment = CustomerGroupAssignment::query()
                ->where('customer_id', $customer->getKey())
                ->where('customer_group_id', $application->assigned_group_id)
                ->first();
        }

        return Theme::scope('wholesale.customer.wholesale-dashboard', [
            'application' => $application,
            'groupAssignment' => $groupAssignment,
            'customer' => $customer,
        ], 'plugins/ecommerce-wholesale::themes.customer.wholesale-dashboard')->render();
    }

    public function showReapplyForm(ReapplicationService $service)
    {
        abort_unless(WholesaleHelper::isRegistrationEnabled(), 404);

        $customer = auth('customer')->user();

        if (! $service->canReapply($customer)) {
            return redirect()
                ->route('customer.wholesale.index')
                ->with('error', trans('plugins/ecommerce-wholesale::wholesale.frontend.not_eligible_reapply'));
        }

        $application = $service->getRejectedApplication($customer);

        SeoHelper::setTitle(trans('plugins/ecommerce-wholesale::wholesale.frontend.reapply_for_wholesale'));

        Theme::breadcrumb()
            ->add(trans('plugins/ecommerce-wholesale::wholesale.frontend.home'), route('public.index'))
            ->add(trans('plugins/ecommerce-wholesale::wholesale.frontend.account'), route('customer.overview'))
            ->add(trans('plugins/ecommerce-wholesale::wholesale.frontend.wholesale_account'), route('customer.wholesale.index'))
            ->add(trans('plugins/ecommerce-wholesale::wholesale.frontend.reapply'));

        return Theme::scope('wholesale.customer.reapply-form', [
            'application' => $application,
            'customer' => $customer,
        ], 'plugins/ecommerce-wholesale::themes.customer.reapply-form')->render();
    }

    public function reapply(ReapplyWholesaleRequest $request, ReapplicationService $service)
    {
        abort_unless(WholesaleHelper::isRegistrationEnabled(), 404);

        $customer = auth('customer')->user();

        if (! $service->canReapply($customer)) {
            return $this->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/ecommerce-wholesale::wholesale.frontend.not_eligible_reapply'));
        }

        try {
            $service->createReapplication($customer, $request->validated());
        } catch (\RuntimeException $e) {
            return $this->httpResponse()
                ->setError()
                ->setMessage($e->getMessage());
        }

        return $this->httpResponse()
            ->setMessage(trans('plugins/ecommerce-wholesale::wholesale.frontend.reapplication_submitted'))
            ->setNextUrl(route('customer.wholesale.index'));
    }
}
