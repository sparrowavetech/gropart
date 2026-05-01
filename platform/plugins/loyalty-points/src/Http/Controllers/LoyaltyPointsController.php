<?php

namespace Botble\LoyaltyPoints\Http\Controllers;

use Botble\Base\Supports\Breadcrumb;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Illuminate\Support\Facades\DB;

class LoyaltyPointsController extends BaseLoyaltyController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/loyalty-points::loyalty-points.reports.menu_name'), route('loyalty-points.index'));
    }

    public function index()
    {
        $this->pageTitle(trans('plugins/loyalty-points::loyalty-points.reports.page_title'));

        $totalCustomers = CustomerPointBalance::query()->count();
        $activeCustomers = CustomerPointBalance::query()->where('total_points', '>', 0)->count();
        $totalPointsEarned = PointTransaction::query()
            ->where('type', PointTransaction::TYPE_EARN)
            ->sum('points');
        $totalPointsRedeemed = abs(PointTransaction::query()
            ->where('type', PointTransaction::TYPE_REDEEM)
            ->sum('points'));
        $totalPointsInCirculation = CustomerPointBalance::query()->sum('total_points');
        $lifetimePointsEarned = CustomerPointBalance::query()->sum('lifetime_points');

        $topCustomers = CustomerPointBalance::query()
            ->with('customer:id,name,email')
            ->where('total_points', '>', 0)
            ->orderByDesc('total_points')
            ->limit(10)
            ->get();

        $topLifetimeCustomers = CustomerPointBalance::query()
            ->with('customer:id,name,email')
            ->where('lifetime_points', '>', 0)
            ->orderByDesc('lifetime_points')
            ->limit(10)
            ->get();

        $recentTransactions = PointTransaction::query()
            ->with(['customer', 'order'])
            ->latest('created_at')
            ->limit(20)
            ->get();

        $pointsActivityByType = PointTransaction::query()
            ->select('type', DB::raw('SUM(ABS(points)) as total_points'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        $dailyActivity = PointTransaction::query()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CASE WHEN type = "earn" THEN points ELSE 0 END) as earned'),
                DB::raw('SUM(CASE WHEN type = "redeem" THEN ABS(points) ELSE 0 END) as redeemed')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        return view('plugins/loyalty-points::reports.index', compact(
            'totalCustomers',
            'activeCustomers',
            'totalPointsEarned',
            'totalPointsRedeemed',
            'totalPointsInCirculation',
            'lifetimePointsEarned',
            'topCustomers',
            'topLifetimeCustomers',
            'recentTransactions',
            'pointsActivityByType',
            'dailyActivity'
        ));
    }
}
