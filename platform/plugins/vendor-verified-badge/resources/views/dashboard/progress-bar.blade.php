@php
    $customerId = auth('customer')->user()->id;
    $statusData = \SparroWave\VendorVerifiedBadge\Supports\VendorBadgeHelper::isVendorProfileComplete($customerId);
    $isComplete = $statusData['status'];
    $profileScore = $statusData['completePercentage'];
    $storeVerified = $statusData['storeVerified'];
    $appUrl = \SparroWave\VendorVerifiedBadge\Supports\VendorBadgeHelper::getApplicationUrl();
    $badgeIconUrl = \SparroWave\VendorVerifiedBadge\Supports\VendorBadgeHelper::getBadgeIconUrl();
@endphp

@if (!$storeVerified)
    <div class="alert alert-warning bg-light d-flex align-items-center mb-3 p-3 border" role="alert">
        <img class="verified-store-main me-2" style="max-height: 22px; width: auto;" src="{{ $badgeIconUrl }}" alt="{{ __('Verified') }}">
        <h5 class="fw-bold mb-0">
            <a class="fw-bold text-decoration-underline" href="{{ $appUrl }}" target="_blank">{{ __('Apply for green tick') }}</a>
            {{ __('verification badge') }}
        </h5>
    </div>
@endif

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body p-4">
        @if ($isComplete)
            <div class="alert alert-success d-flex align-items-center mb-3" role="alert">
                <i class="icon-check-circle me-2" style="font-size: 20px;"></i>
                <div>
                    <h5 class="alert-heading mb-1">{{ __('Congratulations! Your store profile is :score% completed.', ['score' => $profileScore]) }}</h5>
                    <p class="mb-0 text-muted">{{ __('Your account is fully active. You can now add and manage your products.') }}</p>
                </div>
            </div>
            <div class="progress mb-3" style="height: 16px; font-size: 13px; font-weight: 700; border-radius: 8px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $profileScore }}%;" aria-valuenow="{{ $profileScore }}" aria-valuemin="0" aria-valuemax="100">
                    {{ $profileScore }}%
                </div>
            </div>
        @else
            <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
                <i class="icon-exclamation me-2" style="font-size: 20px;"></i>
                <div>
                    <h5 class="alert-heading mb-1 text-danger">{{ __('Your store profile is only :score% completed!', ['score' => $profileScore]) }}</h5>
                    <p class="mb-0 text-muted">
                        {{ __('Please complete your business, tax, and address details to unlock all features.') }}
                        <a class="fw-bold text-decoration-underline ms-1" href="{{ route('marketplace.vendor.settings') }}">
                            {{ __('Click Here to complete profile') }} &rarr;
                        </a>
                    </p>
                </div>
            </div>
            <div class="progress mb-3" style="height: 16px; font-size: 13px; font-weight: 700; border-radius: 8px;">
                <div class="progress-bar bg-warning text-dark" role="progressbar" style="width: {{ $profileScore }}%;" aria-valuenow="{{ $profileScore }}" aria-valuemin="0" aria-valuemax="100">
                    {{ $profileScore }}%
                </div>
            </div>
        @endif
    </div>
</div>
