<?php

if (! defined('FAQ_CATEGORY_MODULE_SCREEN_NAME')) {
    define('FAQ_CATEGORY_MODULE_SCREEN_NAME', 'faq_category');
}

if (! defined('FAQ_MODULE_SCREEN_NAME')) {
    define('FAQ_MODULE_SCREEN_NAME', 'faq');
}

if (! function_exists('get_faq_category')) {
    /**
     * @param array $conditions
     * @param array $with
     * @param array $withCount
     * @return Collection
    {
        return app(FaqCategoryInterface::class)->advancedGet([
            'condition' => $conditions,
            'order_by' => [
                'order' => 'ASC',
                'created_at' => 'DESC',
            ]
        ]);
    }
}

if (! function_exists('get_faqs_by_category')) {
    /**
     * @param array $conditions
     * @param array $with
     * @param array $withCount
     * @return Collection
     */
    function get_faqs_by_category(array $conditions = [], array $with = [''], array $withCount = [])
    {
        return app(FaqInterface::class)->advancedGet([
            'condition' => $conditions,
            'order_by' => [
                'created_at' => 'DESC',
            ]
        ]);
    }
}
