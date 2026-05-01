<?php

namespace Botble\LoyaltyPoints\Http\Controllers\Fronts;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Customer;
use Botble\LoyaltyPoints\Helpers\LoyaltyHelper;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\LoyaltyLevel;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Botble\LoyaltyPoints\Services\LoyaltyCardService;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Auth;

class LoyaltyPointController extends BaseController
{
    public function __construct(
        protected LoyaltyHelper $loyaltyHelper,
        protected LoyaltyCardService $loyaltyCardService
    ) {
    }

    public function index()
    {
        /**
         * @var Customer $customer
         */
        $customer = Auth::guard('customer')->user();

        abort_unless($customer, 404);

        SeoHelper::setTitle(trans('plugins/loyalty-points::loyalty-points.page_titles.my_loyalty_points'));

        $balance = CustomerPointBalance::query()->firstOrCreate(
            ['customer_id' => $customer->id],
            ['total_points' => 0, 'lifetime_points' => 0]
        );

        $balance->load('level');

        $transactions = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->with('order:id,code')
            ->latest()
            ->paginate(20);

        Theme::breadcrumb()
            ->add(trans('plugins/loyalty-points::loyalty-points.breadcrumbs.home'), route('public.index'))
            ->add(trans('plugins/loyalty-points::loyalty-points.breadcrumbs.my_account'), route('customer.overview'))
            ->add(trans('plugins/loyalty-points::loyalty-points.breadcrumbs.loyalty_points'));

        $nextLevel = LoyaltyLevel::query()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->where('min_points', '>', $balance->lifetime_points)
            ->orderBy('min_points')
            ->first();

        $qrCodeSvg = $this->loyaltyCardService->getQrCodeSvg($customer);

        return Theme::scope(
            'loyalty-points.loyalty-points',
            compact('balance', 'transactions', 'nextLevel', 'qrCodeSvg', 'customer'),
            'plugins/loyalty-points::themes.loyalty-points'
        )
            ->render();
    }
}
