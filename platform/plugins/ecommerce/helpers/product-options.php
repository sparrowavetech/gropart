<?php

use Botble\Ecommerce\Models\Product;

if (! function_exists('render_product_options')) {
    function render_product_options(Product $product): string
    {
        $product->loadMissing(['options', 'options.values']);

        if (! $product->options) {
            return '';
        }

        $html = '<div class="pr_switch_wrap" id="product-option">';

        $script = 'vendor/core/plugins/ecommerce/js/change-product-options.js';

        Theme::asset()->container('footer')->add('change-product-options', $script, ['jquery']);

        foreach ($product->options as $option) {
            $typeClass = __NAMESPACE__ . '\\' . $option->option_type;
            if (class_exists($typeClass)) {
                $instance = new $typeClass();
                $html .= $instance->setOption($option)->setProduct($product)->render();
            } else {
                Log::error(sprintf('Class %s not found', $typeClass));
            }
        }

        $html .= '</div>';

        if (! request()->ajax()) {
            return $html;
        }

        return $html . Html::script($script)->toHtml();
    }
}

if (! function_exists('render_product_options_info')) {
    function render_product_options_info(array $productOption, Product $product, bool $displayBasePrice = false): string
    {
        $view = 'plugins/ecommerce::themes.options.render-options-info';

        $themeView = Theme::getThemeNamespace() . '::views.ecommerce.options.render-options-info';

        if (view()->exists($themeView)) {
            $view = $themeView;
        }

        return view($view, [
            'productOptions' => $productOption,
            'product' => $product,
            'displayBasePrice' => $displayBasePrice,
        ])->render();
    }
}
