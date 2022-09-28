<?php

use Botble\Widget\AbstractWidget;

class AdsWidget extends AbstractWidget
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
    protected $widgetDirectory = 'ads';

    /**
     * AdsWidget constructor.
     */
    public function __construct()
    {
        parent::__construct([
            'name'        => __('Ads'),
            'description' => __('Display Ads on sidebar'),
            'ads_key'     => '',
            'background'  => '',
            'size'        => 'full-width',
        ]);
    }
}
