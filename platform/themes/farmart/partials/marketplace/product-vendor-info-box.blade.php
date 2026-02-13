@if (is_plugin_active('marketplace') && $product->store_id && theme_option('product_page_vendor_info_enabled', 'no') == 'yes')
    @php
        $store = $product->store;
        $showPhone = theme_option('product_page_vendor_info_show_phone', 'yes') == 'yes';
        $showEmail = theme_option('product_page_vendor_info_show_email', 'yes') == 'yes';
        $showWhatsapp = theme_option('product_page_vendor_info_show_whatsapp', 'yes') == 'yes';
        $showAddress = theme_option('product_page_vendor_info_show_address', 'no') == 'yes';

        $socialLinks = $store->getMetaData('social_links', true);
        $whatsappLink = $socialLinks ? Arr::get($socialLinks, 'whatsapp') : null;

        $hasContactInfo = ($showPhone && $store->phone && !MarketplaceHelper::hideStorePhoneNumber())
            || ($showEmail && $store->email && !MarketplaceHelper::hideStoreEmail())
            || ($showWhatsapp && $whatsappLink)
            || ($showAddress && $store->full_address && !MarketplaceHelper::hideStoreAddress());
    @endphp

    @if ($hasContactInfo)
        <div class="row bg-light mb-4 g-0">
            <div class="col-12">
                <div class="px-3 py-4">
                    @if ($title = theme_option('product_page_vendor_info_title'))
                        <h6 class="fw-bold">{{ $title }}</h6>
                    @endif
                    @if ($subtitle = theme_option('product_page_vendor_info_subtitle'))
                        <p class="text">{{ $subtitle }}</p>
                    @endif
                    @if ($showPhone && $store->phone && !MarketplaceHelper::hideStorePhoneNumber())
                        <h4 class="fw-bold"><a href="tel:{{ $store->phone }}">{{ $store->phone }}</a></h4>
                    @endif
                    @if ($showEmail && $store->email && !MarketplaceHelper::hideStoreEmail())
                        <p class="text"><a href="mailto:{{ $store->email }}">{{ $store->email }}</a></p>
                    @endif
                    @if ($showWhatsapp && $whatsappLink)
                        <p class="text"><a href="{{ $whatsappLink }}" target="_blank" rel="noopener noreferrer">{{ __('WhatsApp') }}</a></p>
                    @endif
                    @if ($showAddress && $store->full_address && !MarketplaceHelper::hideStoreAddress())
                        <p class="text">{{ $store->full_address }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endif
