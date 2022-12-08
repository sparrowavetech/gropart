<?php

use Botble\Widget\AbstractWidget;

class BlogTagsWidget extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * @var string
     */
    protected $widgetDirectory = 'blog-tags';

    /**
     * BlogTagsWidget constructor.
     */
    public function __construct()
    {
        parent::__construct([
            'name' => __('Blog Tags'),
            'description' => __('Blog - Popular tags'),
            'number_display' => 5,
        ]);
    }
}
