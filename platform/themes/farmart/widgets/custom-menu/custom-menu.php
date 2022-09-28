<?php

use Botble\Widget\AbstractWidget;

class CustomMenuWidget extends AbstractWidget
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
    protected $widgetDirectory = 'custom-menu';

    /**
     * CustomMenuWidget constructor.
     */
    public function __construct()
    {
        parent::__construct([
            'name'              => __('Custom Menu'),
            'description'       => __('Add a custom menu to your widget area.'),
            'menu_id'           => null,
            'parent_class_name' => 'row-cols-md-3 row-cols-sm-2 row-cols-1'
        ]);
    }
}
