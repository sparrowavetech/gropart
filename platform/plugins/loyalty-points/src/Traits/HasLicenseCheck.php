<?php

namespace Botble\LoyaltyPoints\Traits;

use Botble\LoyaltyPoints\Services\LicenseEncryptionService;
use Illuminate\Http\RedirectResponse;

trait HasLicenseCheck
{
    protected function checkLicenseActivation(): bool
    {
        $licenseStatus = setting('loyalty_points_license_status');
        $purchaseCode = setting('loyalty_points_license_purchase_code');
        $activatedAt = setting('loyalty_points_license_activated_at');

        if ($licenseStatus !== 'activated' || ! $purchaseCode || ! $activatedAt) {
            return false;
        }

        return LicenseEncryptionService::isPurchaseCodeEncrypted($purchaseCode) || ! empty($purchaseCode);
    }

    protected function redirectToLicenseActivation(?string $message = null): RedirectResponse
    {
        $defaultMessage = trans('plugins/loyalty-points::loyalty-points.license.activation_required_message');

        return redirect()
            ->route('loyalty-points.license.index')
            ->with('warning', $message ?: $defaultMessage);
    }

    protected function handleLicenseCheck(): ?RedirectResponse
    {
        if (! $this->checkLicenseActivation()) {
            return $this->redirectToLicenseActivation();
        }

        return null;
    }

    protected function decryptPurchaseCode(string $purchaseCode): string
    {
        return LicenseEncryptionService::decryptPurchaseCode($purchaseCode);
    }

    protected function encryptPurchaseCode(string $purchaseCode): string
    {
        return LicenseEncryptionService::encryptPurchaseCode($purchaseCode);
    }
}
