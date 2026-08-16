<?php

namespace Ashikul\IndiaSmsGateway\Http\Controllers;

use Ashikul\IndiaSmsGateway\Services\SettingsRepository;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdminNotificationsController extends Controller
{
    public function index(SettingsRepository $settings)
    {
        page_title()->setTitle('Admin SMS Notifications');

        return view('plugins/india-sms-gateway::admin-notifications-v116', compact('settings'));
    }

    public function update(Request $request, SettingsRepository $settings)
    {
        $data = $request->validate([
            'admin_phone_numbers' => ['required_if:admin_new_order_sms,1', 'nullable', 'string', 'max:1000'],
        ], [
            'admin_phone_numbers.required_if' => 'Enter at least one admin mobile number when new-order admin SMS is enabled.',
        ]);

        $data['admin_new_order_sms'] = $request->boolean('admin_new_order_sms');
        $settings->set($data);

        return redirect()
            ->route('india-sms.admin-notifications')
            ->with('success_msg', 'Admin SMS recipient settings updated.');
    }
}
