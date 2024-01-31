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

    public static $langPath = 'plugins/sc-contact-manager::enums.simple_status';
}
