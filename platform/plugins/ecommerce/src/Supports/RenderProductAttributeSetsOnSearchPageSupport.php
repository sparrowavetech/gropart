<?php

namespace Botble\Ecommerce\Supports;

use Botble\Ecommerce\Facades\EcommerceHelper as EcommerceHelperFacade;
use Botble\Ecommerce\Models\ProductAttributeSet;
use Illuminate\Support\Arr;

class RenderProductAttributeSetsOnSearchPageSupport
{
    public function render(array $params = []): string
    {
        if (! EcommerceHelperFacade::isEnabledFilterProductsByAttributes()) {
            return '';
        }

        $params = array_merge(['view' => 'plugins/ecommerce::themes.attributes.attributes-filter-renderer'], $params);

        $with = ['attributes', 'categories:id'];

        if (is_plugin_active('language') && is_plugin_active('language-advanced')) {
            $with[] = 'attributes.translations';
        }

        $attributeSets = ProductAttributeSet::query()
            ->where('is_searchable', true)
            ->wherePublished()
            ->orderBy('order')
            ->with($with)
            ->get();

        $selectedAttrs = [];

        $attributesInput = (array) request()->input('attributes', []);

        if (! array_is_list($attributesInput)) {
            foreach ($attributeSets as $attributeSet) {
                $selectedAttrs[$attributeSet->slug] = Arr::get($attributesInput, $attributeSet->slug, []);
            }
        } else {
            $selectedAttrs = $attributesInput;
        }

        return view($params['view'], array_merge($params, compact('attributeSets', 'selectedAttrs')))->render();
    }
}
