<?php

namespace Skillcraft\ContactManager\Enums;

use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static ContactDataTypeEnum WORK()
 * @method static ContactDataTypeEnum HOME()
 * @method static ContactDataTypeEnum BILLING()
 * @method static ContactDataTypeEnum SHIPPING()
 * @method static ContactDataTypeEnum OTHER()
 */
class ContactDataTypeEnum extends Enum
{
    public const WORK = 'work';
    public const HOME = 'home';
    public const BILLING = 'billing';
    public const SHIPPING = 'shipping';
    public const OTHER = 'other';

    public static $langPath = 'plugins/sc-contact-manager::enums.contact_status';

    public function toHtml(): HtmlString|string
    {
        return $this->value;
    }
}
