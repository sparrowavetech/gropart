<?php

namespace Botble\Ecommerce\Enums;

use Botble\Base\Supports\Enum;
use Html;

/**
 * @method static EnquiryStatusEnum PENDING()
 * @method static EnquiryStatusEnum CONTACTED()
 * @method static EnquiryStatusEnum NOTAVAILABLE()
 * @method static EnquiryStatusEnum REJECT()
 */
class EnquiryStatusEnum extends Enum
{
    public const PENDING = 'pending';
    public const CONTACTED = 'contacted';
    public const NOTAVAILABLE = 'not_available';
    public const REJECT = 'rejected';

    /**
     * @var string
     */
    public static $langPath = 'plugins/ecommerce::order.statuses';

    /**
     * @return string
     */
    public function toHtml()
    {
        switch ($this->value) {
            case self::PENDING:
                return Html::tag('span', self::PENDING()->label(), ['class' => 'label-info status-label'])
                    ->toHtml();
            case self::CONTACTED:
                return Html::tag('span', self::CONTACTED()->label(), ['class' => 'label-warning status-label'])
                    ->toHtml();
            case self::NOTAVAILABLE:
                return Html::tag('span', self::NOTAVAILABLE()->label(), ['class' => 'label-secondary status-label'])
                    ->toHtml();
            case self::REJECT:
                return Html::tag('span', self::REJECT()->label(), ['class' => 'label-danger status-label'])
                    ->toHtml();
            default:
                return parent::toHtml();
        }
    }
}
