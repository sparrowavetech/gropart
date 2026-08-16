<?php

namespace Ashikul\IndiaSmsGateway\Http\Controllers;

use Ashikul\IndiaSmsGateway\Models\SmsLog;
use Ashikul\IndiaSmsGateway\Services\GatewayRegistry;
use Ashikul\IndiaSmsGateway\Services\SettingsRepository;
use Ashikul\IndiaSmsGateway\Services\SystemHealthService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class OverviewController extends Controller
{
    public function index(
        SystemHealthService $health,
        GatewayRegistry $gateways,
        SettingsRepository $settings
    ) {
        page_title()->setTitle('Indian SMS');

        $stats = [
            'today' => 0,
            'sent' => 0,
            'failed' => 0,
            'delivered' => 0,
        ];
        $recentLogs = collect();

        if (Schema::hasTable('india_sms_logs')) {
            $today = SmsLog::query()->whereDate('created_at', today());

            $stats = [
                'today' => (clone $today)->count(),
                'sent' => (clone $today)->whereIn('status', ['accepted', 'sent', 'delivered'])->count(),
                'failed' => (clone $today)->where('status', 'failed')->count(),
                'delivered' => (clone $today)->where('status', 'delivered')->count(),
            ];

            $recentLogs = SmsLog::query()->latest()->limit(10)->get();
        }

        return view('plugins/india-sms-gateway::overview', [
            'stats' => $stats,
            'checks' => $health->checks(),
            'recentLogs' => $recentLogs,
            'gateways' => $gateways->all(),
            'defaultGateway' => $settings->get('default_gateway', 'msg91'),
        ]);
    }

    public function health(string $check, SystemHealthService $health)
    {
        $diagnostic = $health->diagnostic($check);

        abort_if($diagnostic === null, 404);

        page_title()->setTitle($diagnostic['label'] . ' — Diagnostic');

        return view(
            'plugins/india-sms-gateway::health.show',
            compact('diagnostic')
        );
    }
}
