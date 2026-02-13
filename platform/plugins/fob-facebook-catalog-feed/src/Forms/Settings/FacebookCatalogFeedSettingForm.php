<?php

namespace FriendsOfBotble\FacebookCatalogFeed\Forms\Settings;

use Botble\Base\Forms\FieldOptions\AlertFieldOption;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\AlertField;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Setting\Forms\SettingForm;
use FriendsOfBotble\FacebookCatalogFeed\Http\Requests\Settings\FacebookCatalogFeedSettingRequest;

class FacebookCatalogFeedSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setSectionTitle(trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.name'))
            ->setSectionDescription(trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.description'))
            ->setValidatorClass(FacebookCatalogFeedSettingRequest::class)
            ->add(
                'feed_info',
                AlertField::class,
                AlertFieldOption::make()
                    ->content($this->getFeedInfoHtml())
                    ->type('info')
            )
            ->add(
                'general_settings_heading',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content('<h4 class="mb-3">' . trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.general_settings') . '</h4>')
            )
            ->add(
                'fob_facebook_catalog_feed_enabled',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.enabled'))
                    ->helperText(trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.enabled_help'))
                    ->value(setting('fob_facebook_catalog_feed_enabled', true))
            )
            ->add(
                'product_selection_heading',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content('<hr class="my-4"><h4 class="mb-3">' . trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.product_selection') . '</h4>')
            )
            ->add(
                'fob_facebook_catalog_feed_include_out_of_stock',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.include_out_of_stock'))
                    ->helperText(trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.include_out_of_stock_help'))
                    ->value(setting('fob_facebook_catalog_feed_include_out_of_stock', false))
            )
            ->add(
                'fob_facebook_catalog_feed_include_variations',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.include_variations'))
                    ->helperText(trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.include_variations_help'))
                    ->value(setting('fob_facebook_catalog_feed_include_variations', false))
            )
            ->add(
                'product_attributes_heading',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content('<hr class="my-4"><h4 class="mb-3">' . trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.product_attributes') . '</h4>')
            )
            ->add(
                'fob_facebook_catalog_feed_brand_attribute',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.brand_attribute'))
                    ->placeholder(trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.brand_attribute_placeholder'))
                    ->helperText(trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.brand_attribute_help'))
                    ->value(setting('fob_facebook_catalog_feed_brand_attribute'))
            )
            ->add(
                'fob_facebook_catalog_feed_condition',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.condition'))
                    ->helperText(trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.condition_help'))
                    ->choices([
                        'new' => trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.condition_new'),
                        'refurbished' => trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.condition_refurbished'),
                        'used' => trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.condition_used'),
                    ])
                    ->selected(setting('fob_facebook_catalog_feed_condition', 'new'))
            )
            ->add(
                'availability_settings_heading',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content('<hr class="my-4"><h4 class="mb-3">' . trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.availability_settings') . '</h4>')
            )
            ->add(
                'fob_facebook_catalog_feed_availability_in_stock',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.availability_in_stock'))
                    ->helperText(trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.availability_in_stock_help'))
                    ->choices([
                        'in stock' => trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.in_stock'),
                        'available for order' => trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.available_for_order'),
                        'preorder' => trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.preorder'),
                    ])
                    ->selected(setting('fob_facebook_catalog_feed_availability_in_stock', 'in stock'))
            )
            ->add(
                'fob_facebook_catalog_feed_availability_out_of_stock',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.availability_out_of_stock'))
                    ->helperText(trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.availability_out_of_stock_help'))
                    ->choices([
                        'out of stock' => trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.out_of_stock'),
                        'discontinued' => trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.discontinued'),
                    ])
                    ->selected(setting('fob_facebook_catalog_feed_availability_out_of_stock', 'out of stock'))
            );
    }

    protected function getFeedInfoHtml(): string
    {
        $feedUrl = route('facebook-catalog-feed.feed');
        $allProductsUrl = route('facebook-catalog-feed.feed');
        $newProductsUrl = route('facebook-catalog-feed.feed', ['type' => 'new']);
        $featuredProductsUrl = route('facebook-catalog-feed.feed', ['type' => 'featured']);
        $onSaleProductsUrl = route('facebook-catalog-feed.feed', ['type' => 'on_sale']);

        return view('plugins/fob-facebook-catalog-feed::partials.feed-info', compact(
            'feedUrl',
            'allProductsUrl',
            'newProductsUrl',
            'featuredProductsUrl',
            'onSaleProductsUrl'
        ))->render();
    }
}