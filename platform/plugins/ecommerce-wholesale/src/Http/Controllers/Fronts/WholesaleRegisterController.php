<?php

namespace Botble\EcommerceWholesale\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\EcommerceWholesale\Enums\ApplicationStatusEnum;
use Botble\EcommerceWholesale\Facades\WholesaleHelper;
use Botble\EcommerceWholesale\Forms\Fronts\WholesaleRegisterForm;
use Botble\EcommerceWholesale\Http\Requests\WholesaleRegisterRequest;
use Botble\EcommerceWholesale\Models\WholesaleApplication;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\DB;

class WholesaleRegisterController extends BaseController
{
    public function show()
    {
        abort_unless(WholesaleHelper::isRegistrationEnabled(), 404);

        SeoHelper::setTitle(trans('plugins/ecommerce-wholesale::wholesale.register.title'));

        Theme::breadcrumb()
            ->add(trans('plugins/ecommerce-wholesale::wholesale.frontend.home'), route('public.index'))
            ->add(trans('plugins/ecommerce-wholesale::wholesale.register.title'));

        $customer = auth('customer')->user();

        if ($customer) {
            $existingApplication = WholesaleApplication::query()
                ->where('customer_id', $customer->id)
                ->whereIn('status', [ApplicationStatusEnum::PENDING, ApplicationStatusEnum::APPROVED])
                ->first();

            if ($existingApplication) {
                return Theme::scope(
                    'wholesale.already-applied',
                    ['application' => $existingApplication],
                    'plugins/ecommerce-wholesale::themes.already-applied'
                )->render();
            }
        }

        $form = WholesaleRegisterForm::create();

        return Theme::scope(
            'wholesale.register',
            compact('form'),
            'plugins/ecommerce-wholesale::themes.register'
        )->render();
    }

    public function store(WholesaleRegisterRequest $request)
    {
        abort_unless(WholesaleHelper::isRegistrationEnabled(), 404);

        $customer = auth('customer')->user();
        $email = $customer?->email ?? $request->input('email');

        return DB::transaction(function () use ($request, $customer, $email) {
            $existingApplication = WholesaleApplication::query()
                ->where('email', $email)
                ->where('status', ApplicationStatusEnum::PENDING)
                ->lockForUpdate()
                ->first();

            if ($existingApplication) {
                return $this->httpResponse()
                    ->setError()
                    ->setMessage(trans('plugins/ecommerce-wholesale::wholesale.register.already_pending'));
            }

            WholesaleApplication::query()->create([
                'customer_id' => $customer?->id,
                'email' => $email,
                'name' => $customer?->name ?? $request->input('name'),
                'phone' => $request->input('phone'),
                'company_name' => $request->input('company_name'),
                'tax_id' => $request->input('tax_id'),
                'business_type' => $request->input('business_type'),
                'expected_volume' => $request->input('expected_volume'),
                'notes' => $request->input('notes'),
                'status' => ApplicationStatusEnum::PENDING,
            ]);

            return $this->httpResponse()
                ->setMessage(trans('plugins/ecommerce-wholesale::wholesale.messages.application_submitted'))
                ->setNextUrl(route('public.wholesale.success'));
        });
    }

    public function success()
    {
        SeoHelper::setTitle(trans('plugins/ecommerce-wholesale::wholesale.register.success_title'));

        Theme::breadcrumb()
            ->add(trans('plugins/ecommerce-wholesale::wholesale.frontend.home'), route('public.index'))
            ->add(trans('plugins/ecommerce-wholesale::wholesale.register.success_title'));

        return Theme::scope(
            'wholesale.success',
            [],
            'plugins/ecommerce-wholesale::themes.success'
        )->render();
    }
}
