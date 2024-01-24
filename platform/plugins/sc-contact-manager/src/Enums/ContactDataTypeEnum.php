<?php

namespace Skillcraft\ContactManager\Enums;

use Botble\Base\Supports\Enum;

/**
 * @method static ContactDataTypeEnum WORK()
 * @method static ContactDataTypeEnum HOME()
 * @method static ContactDataTypeEnum BILLING()
 * @method static ContactDataTypeEnum SHIPPING()
 * @method static ContactDataTypeEnum OTHER()
 */
class ContactDataTypeEnum extends Enum
{
    public const WORK       = 'work';
    public const HOME       = 'home';
    public const BILLING    = 'billing';
    public const SHIPPING   = 'shipping';
    public const OTHER      = 'other';

    /**
     * @var string
     */
    public static $langPath = 'plugins/sc-contact-manager::enums.contact_status';

    /**
     * @return string
     */
    public function toHtml()
    {
        switch ($this->value) {
            default:
                return $this->value;
        }
    }
}
