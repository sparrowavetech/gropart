<?php

namespace FriendsOfBotble\FacebookCatalogFeed\Http\Requests\Settings;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;

class FacebookCatalogFeedSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'fob_facebook_catalog_feed_enabled' => [new OnOffRule()],
            'fob_facebook_catalog_feed_include_out_of_stock' => [new OnOffRule()],
            'fob_facebook_catalog_feed_include_variations' => [new OnOffRule()],
            'fob_facebook_catalog_feed_brand_attribute' => ['nullable', 'string', 'max:255'],
            'fob_facebook_catalog_feed_condition' => ['required', 'in:new,refurbished,used'],
            'fob_facebook_catalog_feed_availability_in_stock' => ['required', 'in:in stock,available for order,preorder'],
            'fob_facebook_catalog_feed_availability_out_of_stock' => ['required', 'in:out of stock,discontinued'],
        ];
    }
}