<?php

namespace FriendsOfBotble\FacebookCatalogFeed\Widgets;

use Botble\Widget\AbstractWidget;

class FacebookCatalogFeedWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.widget.name'),
            'description' => trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.widget.description'),
        ]);
    }

    public function run(): ?string
    {
        if (! setting('fob_facebook_catalog_feed_enabled', true)) {
            return null;
        }

        return view('plugins/fob-facebook-catalog-feed::widgets.facebook-catalog-feed', [
            'config' => $this->getConfig(),
            'feedUrl' => route('facebook-catalog-feed.feed'),
        ])->render();
    }
}
