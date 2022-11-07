<?php

namespace Botble\Base\Enums;

use Botble\Base\Supports\Enum;
use Html;

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
    public const IS_VERIFIED = 1;
    public const IS_UNVERIFIED = 0;
    public const IS_VERIFIED_LABEL = 'verified';
    public const IS_UNVERIFIED_LABEL = 'un_verified';
    /**
     * @var string
     */
    public static $langPath = 'core/base::enums.statuses';

    /**
     * @return string
     */
    public function toHtml()
    {
        switch ($this->value) {
            case self::DRAFT:
                return Html::tag('span', self::DRAFT()->label(), ['class' => 'label-info status-label'])
                    ->toHtml();
            case self::PENDING:
                return Html::tag('span', self::PENDING()->label(), ['class' => 'label-warning status-label'])
                    ->toHtml();
            case self::PUBLISHED:
                return Html::tag('span', self::PUBLISHED()->label(), ['class' => 'label-success status-label'])
                    ->toHtml();
            case self::IS_VERIFIED:
                return Html::tag('span', self::IS_VERIFIED_LABEL()->label(), ['class' => 'label-success status-label'])
                    ->toHtml();
            case self::IS_UNVERIFIED:
                return Html::tag('span', self::IS_UNVERIFIED_LABEL()->label(), ['class' => 'label-warning status-label'])
                    ->toHtml();
            default:
                return parent::toHtml();
        }
    }
}
