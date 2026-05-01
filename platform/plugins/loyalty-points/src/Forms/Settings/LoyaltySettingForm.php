<?php

namespace Botble\LoyaltyPoints\Forms\Settings;

use Botble\Base\Forms\FieldOptions\AlertFieldOption;
use Botble\Base\Forms\FieldOptions\ColorFieldOption;
use Botble\Base\Forms\FieldOptions\MultiChecklistFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\AlertField;
use Botble\Base\Forms\Fields\ColorField;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\MultiCheckListField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Language\Facades\Language;
use Botble\LoyaltyPoints\Enums\ProductInfoBoxStyleEnum;
use Botble\LoyaltyPoints\Http\Requests\Settings\LoyaltySettingRequest;
use Botble\Setting\Facades\Setting;
use Botble\Setting\Forms\SettingForm;
use Carbon\Carbon;

class LoyaltySettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $isEnabled = get_loyalty_setting('enable_loyalty_program', true);
        $currency = cms_currency()->getApplicationCurrency();
        $currencySymbol = $currency->symbol;
        $currencyCode = $currency->title;

        $this
            ->setSectionTitle(trans('plugins/loyalty-points::loyalty-points.settings.title'))
            ->setSectionDescription(trans('plugins/loyalty-points::loyalty-points.settings.description'))
            ->setValidatorClass(LoyaltySettingRequest::class)
            ->add(
                'loyalty_points_enable_loyalty_program',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.enable_loyalty_program'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.enable_loyalty_program_help'))
                    ->value($isEnabled)
                    ->attributes([
                        'data-bb-toggle' => 'collapse',
                        'data-bb-target' => '.loyalty-settings',
                    ])
            )
            ->add('open_fieldset_loyalty_settings', HtmlField::class, [
                'html' => sprintf(
                    '<fieldset class="form-fieldset loyalty-settings" style="display: %s;" data-bb-value="1">',
                    $isEnabled ? 'block' : 'none'
                ),
            ])
            ->add('earning_section_heading', HtmlField::class, [
                'html' => sprintf(
                    '<div class="alert alert-primary d-flex align-items-center mb-4"><x-core::icon name="ti ti-gift" class="icon-lg me-2" /><div><h5 class="alert-heading mb-1">%s</h5><p class="mb-0">%s</p></div></div>',
                    trans('plugins/loyalty-points::loyalty-points.settings.earning_section'),
                    trans('plugins/loyalty-points::loyalty-points.settings.earning_section_description')
                ),
            ])
            ->add(
                'loyalty_points_points_exchange_rate',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.points_exchange_rate'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.points_exchange_rate_help', [
                        'currency' => $currencySymbol,
                        'amount' => format_price(1, $currency),
                    ]))
                    ->value(get_loyalty_setting('points_exchange_rate', 100))
                    ->attributes([
                        'min' => 1,
                    ])
            )
            ->add(
                'loyalty_points_points_earning_rate',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.points_earning_rate'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.points_earning_rate_help', ['currency' => $currencySymbol]))
                    ->value(get_loyalty_setting('points_earning_rate', 1))
                    ->attributes([
                        'min' => 1,
                    ])
            )
            ->add(
                'loyalty_points_points_earning_currency',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.points_earning_currency', ['currency' => $currencyCode]))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.points_earning_currency_help', [
                        'currency' => $currencySymbol,
                        'amount' => format_price(100, $currency),
                    ]))
                    ->value(get_loyalty_setting('points_earning_currency', 100))
                    ->attributes([
                        'min' => 1,
                    ])
            )
            ->add(
                'loyalty_points_eligible_order_statuses[]',
                MultiCheckListField::class,
                MultiChecklistFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.eligible_order_statuses'))
                    ->choices(OrderStatusEnum::labels())
                    ->selected($this->getEligibleOrderStatuses())
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.eligible_order_statuses_help'))
                    ->inline()
            )
            ->add('bonus_points_section_divider', HtmlField::class, [
                'html' => '<hr class="my-4">',
            ])
            ->add('bonus_points_section_heading', HtmlField::class, [
                'html' => sprintf(
                    '<div class="alert alert-info d-flex align-items-center mb-4"><x-core::icon name="ti ti-star" class="icon-lg me-2" /><div><h5 class="alert-heading mb-1">%s</h5><p class="mb-0">%s</p></div></div>',
                    trans('plugins/loyalty-points::loyalty-points.settings.bonus_points_section'),
                    trans('plugins/loyalty-points::loyalty-points.settings.bonus_points_section_description')
                ),
            ])
            ->add(
                'loyalty_points_points_for_registration',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.points_for_registration'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.points_for_registration_help'))
                    ->value(get_loyalty_setting('points_for_registration', 100))
                    ->attributes([
                        'min' => 0,
                    ])
            )
            ->add(
                'loyalty_points_points_for_review',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.points_for_review'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.points_for_review_help'))
                    ->value(get_loyalty_setting('points_for_review', 50))
                    ->attributes([
                        'min' => 0,
                    ])
            )
            ->add(
                'loyalty_points_points_for_photo_review',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.points_for_photo_review'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.points_for_photo_review_help'))
                    ->value(get_loyalty_setting('points_for_photo_review', 100))
                    ->attributes([
                        'min' => 0,
                    ])
            )
            ->add(
                'loyalty_points_points_for_referral',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.points_for_referral'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.points_for_referral_help'))
                    ->value(get_loyalty_setting('points_for_referral', 300))
                    ->attributes([
                        'min' => 0,
                    ])
            )
            ->add(
                'points_for_referral_affiliate_notice',
                AlertField::class,
                AlertFieldOption::make()
                    ->type('info')
                    ->content(trans('plugins/loyalty-points::loyalty-points.settings.points_for_referral_affiliate_notice'))
            )
            ->add(
                'loyalty_points_points_for_birthday',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.points_for_birthday'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.points_for_birthday_help'))
                    ->value(get_loyalty_setting('points_for_birthday', 200))
                    ->attributes([
                        'min' => 0,
                    ])
            )
            ->add(
                'cronjob_status',
                AlertField::class,
                AlertFieldOption::make()
                    ->type($this->getCronjobStatus()['type'])
                    ->content($this->getCronjobStatus()['message'])
            )
            ->add('redemption_section_divider', HtmlField::class, [
                'html' => '<hr class="my-4">',
            ])
            ->add('redemption_section_heading', HtmlField::class, [
                'html' => sprintf(
                    '<div class="alert alert-success d-flex align-items-center mb-4"><x-core::icon name="ti ti-discount-2" class="icon-lg me-2" /><div><h5 class="alert-heading mb-1">%s</h5><p class="mb-0">%s</p></div></div>',
                    trans('plugins/loyalty-points::loyalty-points.settings.redemption_section'),
                    trans('plugins/loyalty-points::loyalty-points.settings.redemption_section_description')
                ),
            ])
            ->add(
                'loyalty_points_points_redemption_rate',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.points_redemption_rate'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.points_redemption_rate_help', ['currency' => $currencySymbol]))
                    ->value(get_loyalty_setting('points_redemption_rate', 100))
                    ->attributes([
                        'min' => 1,
                    ])
            )
            ->add(
                'loyalty_points_points_redemption_currency',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.points_redemption_currency', ['currency' => $currencyCode]))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.points_redemption_currency_help', [
                        'currency' => $currencySymbol,
                        'amount' => format_price(100, $currency),
                    ]))
                    ->value(get_loyalty_setting('points_redemption_currency', 100))
                    ->attributes([
                        'min' => 1,
                    ])
            )
            ->add('limits_section_heading', HtmlField::class, [
                'html' => sprintf(
                    '<div class="mb-3"><h6 class="text-muted text-uppercase">%s</h6></div>',
                    trans('plugins/loyalty-points::loyalty-points.settings.limits_section')
                ),
            ])
            ->add(
                'loyalty_points_min_redeemable_points',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.min_redeemable_points'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.min_redeemable_points_help'))
                    ->value(get_loyalty_setting('min_redeemable_points', 0))
                    ->attributes([
                        'min' => 0,
                    ])
            )
            ->add(
                'loyalty_points_max_redeemable_points',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.max_redeemable_points'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.max_redeemable_points_help'))
                    ->value(get_loyalty_setting('max_redeemable_points', 0))
                    ->attributes([
                        'min' => 0,
                    ])
            )
            ->add(
                'loyalty_points_max_redemption_percentage',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.max_redemption_percentage'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.max_redemption_percentage_help'))
                    ->value(get_loyalty_setting('max_redemption_percentage', 20))
                    ->attributes([
                        'min' => 0,
                        'max' => 100,
                    ])
            )
            ->add('expiry_section_divider', HtmlField::class, [
                'html' => '<hr class="my-4">',
            ])
            ->add('expiry_section_heading', HtmlField::class, [
                'html' => sprintf(
                    '<div class="alert alert-warning d-flex align-items-center mb-4"><x-core::icon name="ti ti-clock-exclamation" class="icon-lg me-2" /><div><h5 class="alert-heading mb-1">%s</h5><p class="mb-0">%s</p></div></div>',
                    trans('plugins/loyalty-points::loyalty-points.settings.expiry_section'),
                    trans('plugins/loyalty-points::loyalty-points.settings.expiry_section_description')
                ),
            ])
            ->add(
                'loyalty_points_points_expiry_months',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.points_expiry_months'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.points_expiry_months_help'))
                    ->value(get_loyalty_setting('points_expiry_months', 12))
                    ->attributes([
                        'min' => 0,
                    ])
            )
            ->add('customer_features_section_divider', HtmlField::class, [
                'html' => '<hr class="my-4">',
            ])
            ->add('customer_features_section_heading', HtmlField::class, [
                'html' => sprintf(
                    '<div class="alert alert-secondary d-flex align-items-center mb-4"><x-core::icon name="ti ti-user-cog" class="icon-lg me-2" /><div><h5 class="alert-heading mb-1">%s</h5><p class="mb-0">%s</p></div></div>',
                    trans('plugins/loyalty-points::loyalty-points.settings.customer_features_section'),
                    trans('plugins/loyalty-points::loyalty-points.settings.customer_features_section_description')
                ),
            ])
            ->add(
                'loyalty_points_enable_loyalty_card',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.enable_loyalty_card'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.enable_loyalty_card_help'))
                    ->value(get_loyalty_setting('enable_loyalty_card', true))
            )
            ->add(
                'loyalty_points_enable_guest_checkout_member_id',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.enable_guest_checkout_member_id'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.enable_guest_checkout_member_id_help'))
                    ->value(get_loyalty_setting('enable_guest_checkout_member_id', true))
            )
            ->add(
                'loyalty_points_enable_product_info',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.enable_product_info'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.enable_product_info_help'))
                    ->value(get_loyalty_setting('enable_product_info', true))
                    ->attributes([
                        'data-bb-toggle' => 'collapse',
                        'data-bb-target' => '.product-info-style-settings',
                    ])
            )
            ->add('open_fieldset_product_info_style', HtmlField::class, [
                'html' => sprintf(
                    '<fieldset class="form-fieldset product-info-style-settings" style="display: %s;" data-bb-value="1">',
                    get_loyalty_setting('enable_product_info', true) ? 'block' : 'none'
                ),
            ])
            ->add(
                'loyalty_points_product_info_box_style',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.product_info_box_style'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.product_info_box_style_help'))
                    ->choices(ProductInfoBoxStyleEnum::labels())
                    ->selected(get_loyalty_setting('product_info_box_style', ProductInfoBoxStyleEnum::DEFAULT))
            )
            ->add(
                'loyalty_points_product_info_bg_color',
                ColorField::class,
                ColorFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.product_info_bg_color'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.product_info_bg_color_help'))
                    ->value(get_loyalty_setting('product_info_bg_color', '#f8f9fa'))
            )
            ->add(
                'loyalty_points_product_info_text_color',
                ColorField::class,
                ColorFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.product_info_text_color'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.product_info_text_color_help'))
                    ->value(get_loyalty_setting('product_info_text_color', '#6c757d'))
            )
            ->add(
                'loyalty_points_product_info_icon_color',
                ColorField::class,
                ColorFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.product_info_icon_color'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.product_info_icon_color_help'))
                    ->value(get_loyalty_setting('product_info_icon_color', '#2fb344'))
            )
            ->add(
                'loyalty_points_product_info_border_color',
                ColorField::class,
                ColorFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.product_info_border_color'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.product_info_border_color_help'))
                    ->value(get_loyalty_setting('product_info_border_color', '#e0e0e0'))
            )
            ->add(
                'loyalty_points_product_info_border_radius',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.product_info_border_radius'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.product_info_border_radius_help'))
                    ->value(get_loyalty_setting('product_info_border_radius', ''))
                    ->min(0)
                    ->max(50)
            )
            ->add(
                'loyalty_points_product_info_padding',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.product_info_padding'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.product_info_padding_help'))
                    ->value(get_loyalty_setting('product_info_padding', ''))
                    ->min(0)
                    ->max(100)
            )
            ->add('close_fieldset_product_info_style', HtmlField::class, [
                'html' => '</fieldset>',
            ])
            ->add(
                'loyalty_points_customer_page_slug',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.customer_page_slug'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.customer_page_slug_help', [
                        'url' => url(get_loyalty_setting('customer_page_slug', 'customer/loyalty-points')),
                    ]))
                    ->value(get_loyalty_setting('customer_page_slug', 'customer/loyalty-points'))
                    ->placeholder('customer/loyalty-points')
            );

        $this->addLanguageSlugFields();

        $emailEnabled = get_loyalty_setting('enable_email_notification', true);

        $this
            ->add('email_section_divider', HtmlField::class, [
                'html' => '<hr class="my-4">',
            ])
            ->add('email_section_heading', HtmlField::class, [
                'html' => sprintf(
                    '<div class="alert alert-info d-flex align-items-center mb-4"><x-core::icon name="ti ti-mail" class="icon-lg me-2" /><div><h5 class="alert-heading mb-1">%s</h5><p class="mb-0">%s</p></div></div>',
                    trans('plugins/loyalty-points::loyalty-points.settings.email_section'),
                    trans('plugins/loyalty-points::loyalty-points.settings.email_section_description')
                ),
            ])
            ->add(
                'email_config_notice',
                AlertField::class,
                AlertFieldOption::make()
                    ->type('warning')
                    ->content(trans('plugins/loyalty-points::loyalty-points.settings.email_config_notice', [
                        'url' => route('settings.email'),
                    ]))
            )
            ->add(
                'loyalty_points_enable_email_notification',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.enable_email_notification'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.enable_email_notification_help'))
                    ->value($emailEnabled)
                    ->attributes([
                        'data-bb-toggle' => 'collapse',
                        'data-bb-target' => '.email-notification-settings',
                    ])
            )
            ->add('open_fieldset_email_notification', HtmlField::class, [
                'html' => sprintf(
                    '<fieldset class="form-fieldset email-notification-settings" style="display: %s;" data-bb-value="1">',
                    $emailEnabled ? 'block' : 'none'
                ),
            ])
            ->add(
                'loyalty_points_notification_emails',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.notification_emails'))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.notification_emails_help'))
                    ->value(get_loyalty_setting('notification_emails', ''))
                    ->placeholder('admin@example.com, support@example.com')
            )
            ->add('close_fieldset_email_notification', HtmlField::class, [
                'html' => '</fieldset>',
            ]);

        $this->add('close_fieldset_loyalty_settings', HtmlField::class, [
            'html' => '</fieldset>',
        ]);
    }

    protected function addLanguageSlugFields(): void
    {
        if (! is_plugin_active('language')) {
            return;
        }

        $languages = Language::getActiveLanguage(['lang_code', 'lang_name', 'lang_flag', 'lang_locale']);
        $defaultLanguage = Language::getDefaultLanguage();

        if ($languages->count() < 2) {
            return;
        }

        foreach ($languages as $language) {
            if ($defaultLanguage && $language->lang_code === $defaultLanguage->lang_code) {
                continue;
            }

            $this->add(
                'loyalty_points_customer_page_slug_' . $language->lang_locale,
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/loyalty-points::loyalty-points.settings.customer_page_slug_language', [
                        'language' => $language->lang_name,
                    ]))
                    ->helperText(trans('plugins/loyalty-points::loyalty-points.settings.customer_page_slug_language_help', [
                        'language' => $language->lang_name,
                    ]))
                    ->value(get_loyalty_setting('customer_page_slug_' . $language->lang_locale, ''))
                    ->placeholder(trans('plugins/loyalty-points::loyalty-points.settings.customer_page_slug_language_placeholder'))
            );
        }
    }

    protected function getEligibleOrderStatuses(): array
    {
        $statuses = get_loyalty_setting('eligible_order_statuses', ['completed']);

        if (is_array($statuses)) {
            return $statuses;
        }

        return json_decode($statuses, true) ?: ['completed'];
    }

    protected function getCronjobStatus(): array
    {
        $lastRunAt = Setting::get('cronjob_last_run_at');

        if (! $lastRunAt) {
            return [
                'type' => 'warning',
                'message' => trans('plugins/loyalty-points::loyalty-points.settings.cronjob_not_setup', [
                    'url' => route('system.cronjob'),
                ]),
            ];
        }

        $lastRunAt = Carbon::parse($lastRunAt);

        if (Carbon::now()->diffInMinutes($lastRunAt) > 10) {
            return [
                'type' => 'danger',
                'message' => trans('plugins/loyalty-points::loyalty-points.settings.cronjob_not_running', [
                    'url' => route('system.cronjob'),
                ]),
            ];
        }

        return [
            'type' => 'success',
            'message' => trans('plugins/loyalty-points::loyalty-points.settings.cronjob_working', [
                'time' => $lastRunAt->diffForHumans(),
            ]),
        ];
    }
}
