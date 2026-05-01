<?php

namespace Botble\LoyaltyPoints\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Botble\Base\Supports\Core;
use Botble\LoyaltyPoints\Services\LicenseEncryptionService;
use Botble\Setting\Facades\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Throwable;

class LicenseController extends BaseLoyaltyController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/loyalty-points::loyalty-points.license.title'), route('loyalty-points.license.index'));
    }

    public function index()
    {
        $this->pageTitle(trans('plugins/loyalty-points::loyalty-points.license.title'));

        $isLicenseVerified = false;
        $licenseData = null;

        $licenseStatus = setting('loyalty_points_license_status');
        $purchaseCode = setting('loyalty_points_license_purchase_code');
        $activatedAt = setting('loyalty_points_license_activated_at');

        if ($licenseStatus === 'activated' && $purchaseCode && $activatedAt) {
            $isLicenseVerified = true;

            LicenseEncryptionService::migrateExistingPurchaseCode();

            $decryptedPurchaseCode = LicenseEncryptionService::decryptPurchaseCode($purchaseCode);

            $licenseData = [
                'purchase_code' => $decryptedPurchaseCode,
                'activated_at' => Carbon::parse($activatedAt)->format('M d Y'),
            ];
        }

        return view('plugins/loyalty-points::license.index', compact('isLicenseVerified', 'licenseData'));
    }

    public function activate(Request $request): BaseHttpResponse
    {
        $request->validate([
            'purchase_code' => ['required', 'string', 'max:255'],
            'license_rules_agreement' => ['required', 'accepted'],
        ]);

        $purchaseCode = $request->input('purchase_code');

        try {
            $core = Core::make()->getCoreFileData();

            $url = $core['marketplaceUrl'];

            $token = $core['marketplaceToken'];

            $response =
                Http::asJson()
                    ->withHeaders([
                        'Authorization' => 'Token ' . $token,
                    ])
                    ->acceptJson()
                    ->withoutVerifying()
                    ->connectTimeout(100)
                    ->post($url . '/products/license/activate', [
                        'purchase_code' => $purchaseCode,
                        'domain' => rtrim(url('')),
                        'product' => 'botble/loyalty-points',
                    ]);

            if ($response->successful()) {
                $activatedAt = Carbon::now();

                $encryptedPurchaseCode = LicenseEncryptionService::encryptPurchaseCode($purchaseCode);

                Setting::forceSet('loyalty_points_license_purchase_code', $encryptedPurchaseCode)->save();
                Setting::forceSet('loyalty_points_license_activated_at', $activatedAt->toDateTimeString())->save();
                Setting::forceSet('loyalty_points_license_status', 'activated')->save();

                return $this
                    ->httpResponse()
                    ->setMessage(trans('plugins/loyalty-points::loyalty-points.license.activated_successfully'))
                    ->setData([
                        'purchase_code' => $purchaseCode,
                        'activated_at' => $activatedAt->format('M d Y'),
                    ]);
            } else {
                $errorMessage = $response->json('message') ?? trans(
                    'plugins/loyalty-points::loyalty-points.license.invalid_purchase_code'
                );

                return $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage($errorMessage);
            }
        } catch (Throwable) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/loyalty-points::loyalty-points.license.activation_error'));
        }
    }

    public function deactivate(): BaseHttpResponse
    {
        try {
            $core = Core::make()->getCoreFileData();

            $url = $core['marketplaceUrl'];

            $token = $core['marketplaceToken'];

            $purchaseCode = setting('loyalty_points_license_purchase_code');
            $purchaseCode = LicenseEncryptionService::decryptPurchaseCode($purchaseCode);

            $response =
                Http::asJson()
                    ->withHeaders([
                        'Authorization' => 'Token ' . $token,
                    ])
                    ->acceptJson()
                    ->withoutVerifying()
                    ->connectTimeout(100)
                    ->post($url . '/products/license/deactivate', [
                        'purchase_code' => $purchaseCode,
                        'domain' => rtrim(url('')),
                        'product' => 'botble/loyalty-points',
                    ]);

            Setting::forceSet('loyalty_points_license_purchase_code', '')->save();
            Setting::forceSet('loyalty_points_license_activated_at', '')->save();
            Setting::forceSet('loyalty_points_license_status', '')->save();

            if ($response->successful()) {
                return $this
                    ->httpResponse()
                    ->setMessage(trans('plugins/loyalty-points::loyalty-points.license.deactivated_successfully'));
            } else {
                $errorMessage = $response->json('message') ?? trans(
                    'plugins/loyalty-points::loyalty-points.license.deactivation_error'
                );

                return $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage($errorMessage);
            }
        } catch (Throwable) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/loyalty-points::loyalty-points.license.deactivation_error'));
        }
    }
}
