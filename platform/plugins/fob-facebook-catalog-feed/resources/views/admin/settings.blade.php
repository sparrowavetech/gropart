@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="max-width-1200">
        {!! Form::open(['route' => 'fob-facebook-catalog-feed.settings.update']) !!}
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>
                        {{ trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.title') }}
                    </x-core::card.title>
                </x-core::card.header>

                <x-core::card.body>
                    <x-core::alert type="info">
                        {{ trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.feed_url_info') }}
                        <br>
                        <strong>{{ route('facebook-catalog-feed.feed') }}</strong>
                        <br><br>
                        {{ trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.feed_types_info') }}
                        <ul>
                            <li><code>{{ route('facebook-catalog-feed.feed') }}</code> - {{ trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.all_products') }}</li>
                            <li><code>{{ route('facebook-catalog-feed.feed', ['type' => 'new']) }}</code> - {{ trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.new_products') }}</li>
                            <li><code>{{ route('facebook-catalog-feed.feed', ['type' => 'featured']) }}</code> - {{ trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.featured_products') }}</li>
                            <li><code>{{ route('facebook-catalog-feed.feed', ['type' => 'on_sale']) }}</code> - {{ trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.on_sale_products') }}</li>
                        </ul>
                    </x-core::alert>

                    <x-core::form.on-off
                        name="enabled"
                        :label="trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.enabled')"
                        :value="setting('fob_facebook_catalog_feed_enabled', true)"
                    />

                    <x-core::form.on-off
                        name="include_out_of_stock"
                        :label="trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.include_out_of_stock')"
                        :value="setting('fob_facebook_catalog_feed_include_out_of_stock', false)"
                    />

                    <x-core::form.on-off
                        name="include_variations"
                        :label="trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.include_variations')"
                        :value="setting('fob_facebook_catalog_feed_include_variations', false)"
                        :helper-text="trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.include_variations_help')"
                    />

                    <x-core::form.text-input
                        name="brand_attribute"
                        :label="trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.brand_attribute')"
                        :value="setting('fob_facebook_catalog_feed_brand_attribute')"
                        :placeholder="trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.brand_attribute_placeholder')"
                        :helper-text="trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.brand_attribute_help')"
                    />

                    <x-core::form.select
                        name="condition"
                        :label="trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.condition')"
                        :options="[
                            'new' => trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.condition_new'),
                            'refurbished' => trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.condition_refurbished'),
                            'used' => trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.condition_used'),
                        ]"
                        :value="setting('fob_facebook_catalog_feed_condition', 'new')"
                    />

                    <x-core::form.select
                        name="availability_in_stock"
                        :label="trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.availability_in_stock')"
                        :options="[
                            'in stock' => trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.in_stock'),
                            'available for order' => trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.available_for_order'),
                            'preorder' => trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.preorder'),
                        ]"
                        :value="setting('fob_facebook_catalog_feed_availability_in_stock', 'in stock')"
                    />

                    <x-core::form.select
                        name="availability_out_of_stock"
                        :label="trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.availability_out_of_stock')"
                        :options="[
                            'out of stock' => trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.out_of_stock'),
                            'discontinued' => trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.settings.discontinued'),
                        ]"
                        :value="setting('fob_facebook_catalog_feed_availability_out_of_stock', 'out of stock')"
                    />
                </x-core::card.body>

                <x-core::card.footer>
                    <x-core::button type="submit" color="primary">
                        {{ trans('core/base::forms.save_and_continue') }}
                    </x-core::button>
                </x-core::card.footer>
            </x-core::card>
        {!! Form::close() !!}
    </div>
@endsection