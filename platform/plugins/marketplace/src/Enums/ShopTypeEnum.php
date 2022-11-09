<?php

namespace Botble\Marketplace\Enums;

use Botble\Base\Supports\Enum;
use Html;

/**
 * @method static  ShopTypeEnum MANUFACTURE()
 * @method static  ShopTypeEnum WHOLESALER()
 * @method static  ShopTypeEnum RETAILER()
 */
class ShopTypeEnum extends Enum
{
    public const MANUFACTURE = 'manufacture';
    public const WHOLESALER = 'wholesaler';
    public const RETAILER = 'retailer';

    /**
     * @var string
     */
    public static $langPath = 'plugins/marketplace::store.types';

    /**
     * @return string
     */
    public function toHtml()
    {
        switch ($this->value) {
            case self::MANUFACTURE:
                return Html::tag('span', self::MANUFACTURE()->label(), ['class' => 'label-info status-label'])
                    ->toHtml();
            case self::WHOLESALER:
                return Html::tag('span', self::WHOLESALER()->label(), ['class' => 'label-primary status-label'])
                    ->toHtml();
            case self::RETAILER:
                return Html::tag('span', self::RETAILER()->label(), ['class' => 'label-primary status-label'])
                    ->toHtml();
            default:
                return parent::toHtml();
        }
    }
}
