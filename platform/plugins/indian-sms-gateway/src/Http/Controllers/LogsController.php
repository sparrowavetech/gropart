<?php

namespace Ashikul\IndiaSmsGateway\Http\Controllers;

use Ashikul\IndiaSmsGateway\Models\SmsLog;
use Ashikul\IndiaSmsGateway\Services\DatabaseInstaller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;

class LogsController extends Controller
{
    public function index(Request $request, DatabaseInstaller $database)
    {
        page_title()->setTitle('SMS Delivery Logs');
        $database->ensure();

        if (! Schema::hasTable('india_sms_logs')) {
            return view('plugins/india-sms-gateway::logs.index', [
                'logs' => $this->emptyPaginator($request),
                'databaseError' => true,
            ]);
        }

        $query = SmsLog::query()->latest();

        foreach (['gateway', 'status', 'message_type'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->string($field)->toString());
            }
        }

        if ($request->filled('phone')) {
            $query->where('recipient', 'like', '%' . preg_replace('/\D/', '', (string) $request->input('phone')) . '%');
        }

        return view('plugins/india-sms-gateway::logs.index', [
            'logs' => $query->paginate(25)->withQueryString(),
            'databaseError' => false,
        ]);
    }

    public function show(SmsLog $log)
    {
        page_title()->setTitle('SMS Log #' . $log->id);
        return view('plugins/india-sms-gateway::logs.show', compact('log'));
    }

    public function destroy(SmsLog $log)
    {
        $log->delete();
        return redirect()->route('india-sms.logs.index')->with('success_msg', 'SMS log deleted.');
    }

    private function emptyPaginator(Request $request): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, 25, max(1, (int) $request->input('page', 1)), [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    }
}
