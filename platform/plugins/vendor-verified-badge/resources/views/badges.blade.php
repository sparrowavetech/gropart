@if(!empty($store))
    {!! \SparroWave\VendorVerifiedBadge\Supports\VendorBadgeHelper::renderBadges($store, $options ?? []) !!}
@endif
