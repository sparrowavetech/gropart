<?php

namespace FriendsOfBotble\FacebookCatalogFeed\Http\Controllers\Settings;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Setting\Http\Controllers\SettingController;
use FriendsOfBotble\FacebookCatalogFeed\Forms\Settings\FacebookCatalogFeedSettingForm;
use FriendsOfBotble\FacebookCatalogFeed\Http\Requests\Settings\FacebookCatalogFeedSettingRequest;
use Illuminate\Support\Facades\Cache;

class FacebookCatalogFeedSettingController extends SettingController
{
    public function edit()
    {
        $this->pageTitle(trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.name'));

        return FacebookCatalogFeedSettingForm::create()->renderForm();
    }

    public function update(FacebookCatalogFeedSettingRequest $request)
    {
        // Clear feed caches when settings are updated
        Cache::forget('facebook_catalog_feed_all');
        Cache::forget('facebook_catalog_feed_new');
        Cache::forget('facebook_catalog_feed_featured');
        Cache::forget('facebook_catalog_feed_on_sale');
        
        return $this->performUpdate($request->validated());
    }
}