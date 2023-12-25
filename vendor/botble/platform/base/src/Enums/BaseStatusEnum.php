<?php

namespace Botble\Base\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static BaseStatusEnum DRAFT()
 * @method static BaseStatusEnum PUBLISHED()
 * @method static BaseStatusEnum PENDING()
 * @method static BaseStatusEnum IS_VERIFIED_LABEL()
 * @method static BaseStatusEnum IS_UNVERIFIED_LABEL()
 */
class BaseStatusEnum extends Enum
{
    public const PUBLISHED = 'published';
    public const DRAFT = 'draft';
    public const PENDING = 'pending';
    public const IS_VERIFIED_LABEL = 'is_verified_label';
    public const IS_UNVERIFIED_LABEL = 'is_unverified_label';

    public static $langPath = 'core/base::enums.statuses';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::DRAFT => Html::tag('span', self::DRAFT()->label(), ['class' => 'badge bg-secondary text-secondary-fg']),
            self::PENDING => Html::tag('span', self::PENDING()->label(), ['class' => 'badge bg-warning text-warning-fg']),
            self::PUBLISHED => Html::tag('span', self::PUBLISHED()->label(), ['class' => 'badge bg-success text-success-fg']),
            self::IS_VERIFIED_LABEL => Html::tag('span', self::IS_VERIFIED_LABEL()->label(), ['class' => 'badge bg-success text-success-fg']),
            self::IS_UNVERIFIED_LABEL => Html::tag('span', self::IS_UNVERIFIED_LABEL()->label(), ['class' => 'badge bg-danger text-success-fg']),
            default => parent::toHtml(),
        };
    }
}
