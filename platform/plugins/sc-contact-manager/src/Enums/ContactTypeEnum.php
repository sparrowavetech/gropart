<?php

namespace Skillcraft\ContactManager\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static ContactDataTypeEnum CUSTOMER()
 * @method static ContactDataTypeEnum LEAD()
 */
class ContactTypeEnum extends Enum
{
    public const CUSTOMER   = 'customer';
    public const LEAD       = 'lead';

    /**
     * @var string
     */
    public static $langPath = 'plugins/sc-contact-manager::enums.contact_type';

    public function toHtml(): HtmlString|string
    {
        return match ($this->value) {
            self::CUSTOMER => Html::tag('span', self::CUSTOMER()->label(), ['class' => 'label-success status-label'])
                ->toHtml(),
            self::LEAD => Html::tag('span', self::LEAD()->label(), ['class' => 'label-info status-label'])
                ->toHtml(),
            default => parent::toHtml(),
        };
    }
}
