<?php

namespace Botble\Newsletter\Enums;

use Botble\Base\Supports\Enum;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

/**
 * @method static NewsletterStatusEnum SUBSCRIBED()
 * @method static NewsletterStatusEnum UNSUBSCRIBED()
 */
class NewsletterStatusEnum extends Enum
{
    public const SUBSCRIBED = 'subscribed';

    public const UNSUBSCRIBED = 'unsubscribed';

    public static $langPath = 'plugins/newsletter::newsletter.statuses';

    public function toHtml(): HtmlString|string
    {
        $color = match ($this->value) {
            self::SUBSCRIBED => 'success',
            self::UNSUBSCRIBED => 'warning',
            default => null,
        };

        return Blade::render(sprintf('<x-core::badge label="%s" color="%s" />', $this->label(), $color));
    }
}
