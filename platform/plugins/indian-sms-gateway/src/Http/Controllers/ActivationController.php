<?php

namespace Ashikul\IndiaSmsGateway\Http\Controllers;

use Ashikul\IndiaSmsGateway\Services\LicenseManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ActivationController extends Controller
{
    public function index(LicenseManager $licenses)
    {
        page_title()->setTitle('License');

        return view('plugins/india-sms-gateway::activation.index', [
            'license' => $licenses->status(),
        ]);
    }

    public function store(Request $request, LicenseManager $licenses)
    {
        $validated = $request->validate([
            'activation_key' => ['required', 'string', 'max:100'],
        ]);

        [$success, $message] = $licenses->activate($validated['activation_key']);

        if (! $success) {
            return back()->withInput()->with('error_msg', $message);
        }

        return redirect()->route('india-sms.index')->with('success_msg', $message);
    }

    public function refresh(LicenseManager $licenses)
    {
        [$success, $message] = $licenses->refresh();

        return back()->with($success ? 'success_msg' : 'error_msg', $message);
    }

    public function destroy(LicenseManager $licenses)
    {
        [$success, $message] = $licenses->deactivate();

        return redirect()->route('india-sms.activation.index')
            ->with($success ? 'success_msg' : 'warning_msg', $message);
    }
}
