<?php

namespace Skillcraft\ContactManager\Enums;

use Botble\Base\Supports\Enum;

/**
 * @method static SimpleStatusEnum ACTIVE()
 * @method static SimpleStatusEnum DEACTIVE()
 */
class SimpleStatusEnum extends Enum
{
    public const ACTIVE = 'active';
    public const DEACTIVE = 'deactive';

    /**
     * @var string
     */
    public static $langPath = 'plugins/sc-contact-manager::enums.simple_status';

    /**
     * @return string
     */
    public function toHtml()
    {
        switch ($this->value) {
            default:
                return parent::toHtml();
        }
    }
}
