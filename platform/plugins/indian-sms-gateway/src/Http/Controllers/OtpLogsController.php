<?php

namespace Ashikul\IndiaSmsGateway\Http\Controllers;

use Ashikul\IndiaSmsGateway\Models\SmsOtp;
use Ashikul\IndiaSmsGateway\Services\DatabaseInstaller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Throwable;

class OtpLogsController extends Controller
{
    public function index(Request $request, DatabaseInstaller $database)
    {
        page_title()->setTitle('OTP Verification Logs');
        $database->ensure();

        if (! Schema::hasTable('india_sms_otps')) {
            return view('plugins/india-sms-gateway::otps.index', [
                'otps' => $this->emptyPaginator($request),
                'databaseError' => true,
            ]);
        }

        $query = SmsOtp::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('purpose')) {
            $query->where('purpose', $request->string('purpose')->toString());
        }

        $otps = $query->paginate(25)->through(function (SmsOtp $otp): SmsOtp {
            try {
                $phone = Crypt::decryptString($otp->phone_encrypted);
                $otp->masked_phone = substr($phone, 0, 5) . '****' . substr($phone, -3);
            } catch (Throwable) {
                $otp->masked_phone = 'Encrypted';
            }

            return $otp;
        });

        return view('plugins/india-sms-gateway::otps.index', [
            'otps' => $otps,
            'databaseError' => false,
        ]);
    }

    private function emptyPaginator(Request $request): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, 25, max(1, (int) $request->input('page', 1)), [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    }
}
