<?php

namespace FriendsOfBotble\FacebookCatalogFeed\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use FriendsOfBotble\FacebookCatalogFeed\Services\FacebookCatalogFeedService;
use Illuminate\Http\Request;

class FacebookCatalogFeedController extends BaseController
{
    public function __construct(
        protected FacebookCatalogFeedService $facebookCatalogFeedService
    ) {
    }

    public function index(Request $request)
    {
        if (! setting('fob_facebook_catalog_feed_enabled', true)) {
            abort(404);
        }

        $type = $request->input('type', 'all');

        // Stream the response directly without caching for large feeds
        return $this->facebookCatalogFeedService->streamFeed($type);
    }

}
