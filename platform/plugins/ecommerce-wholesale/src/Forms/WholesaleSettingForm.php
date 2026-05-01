<?php

namespace Botble\EcommerceWholesale\Forms;

use Botble\Base\Forms\FieldOptions\CheckboxFieldOption;
use Botble\Base\Forms\FieldOptions\ColorFieldOption;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\Fields\ColorField;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\WholesaleDisplayModeEnum;
use Botble\EcommerceWholesale\Enums\WholesaleStyleEnum;
use Botble\EcommerceWholesale\Facades\WholesaleHelper;
use Botble\EcommerceWholesale\Http\Requests\WholesaleSettingRequest;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\Setting\Forms\SettingForm;

class WholesaleSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $groups = CustomerGroup::query()
            ->where('status', CustomerGroupStatusEnum::PUBLISHED)
            ->orderBy('priority')
            ->orderBy('id')
            ->pluck('name', 'id')
            ->all();

        $enabledValue = setting('wholesale_enabled', true);

        $licenseSection = view('plugins/ecommerce-wholesale::settings.license-section')->render();

        $this
            ->setSectionTitle(trans('plugins/ecommerce-wholesale::wholesale.settings'))
            ->setSectionDescription(trans('plugins/ecommerce-wholesale::wholesale.settings_description'))
            ->setValidatorClass(WholesaleSettingRequest::class)
            ->add(
                'license_section',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content($licenseSection)
            )
            ->add(
                'wholesale_enabled',
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.enabled'))
                    ->value($enabledValue)
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.enabled_help'))
            )
            ->addOpenCollapsible('wholesale_enabled', '1', $enabledValue)
            ->add(
                'wholesale_require_approval',
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.require_approval'))
                    ->value(setting('wholesale_require_approval', true))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.require_approval_help'))
            )
            ->add(
                'wholesale_enable_registration',
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.enable_registration'))
                    ->value(setting('wholesale_enable_registration', true))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.enable_registration_help'))
            )
            ->add(
                'wholesale_show_prices_to_guests',
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.show_prices_to_guests'))
                    ->value(setting('wholesale_show_prices_to_guests', false))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.show_prices_to_guests_help'))
            )
            ->add(
                'wholesale_allow_multiple_groups',
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.allow_multiple_groups'))
                    ->value(setting('wholesale_allow_multiple_groups', false))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.allow_multiple_groups_help'))
            )
            ->add(
                'wholesale_enable_for_guests',
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.enable_for_guests'))
                    ->value(setting('wholesale_enable_for_guests', false))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.enable_for_guests_help'))
            );

        if (is_plugin_active('marketplace')) {
            $this->add(
                'wholesale_enable_vendor_dashboard',
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.enable_vendor_dashboard'))
                    ->value(setting('wholesale_enable_vendor_dashboard', false))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.enable_vendor_dashboard_help'))
            );
        }

        if (! empty($groups)) {
            $this->add(
                'wholesale_default_group',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.default_group'))
                    ->choices(['' => trans('plugins/ecommerce-wholesale::wholesale.select_group')] + $groups)
                    ->selected(setting('wholesale_default_group'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.default_group_help'))
            );
        }

        $showIconEnabled = WholesaleHelper::showIcon();
        $showSavingsEnabled = WholesaleHelper::showSavings();

        $this
            ->add(
                'appearance_section',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content('<h4 class="mb-3">' . trans('plugins/ecommerce-wholesale::wholesale.appearance.section') . '</h4>')
            )
            ->add(
                'wholesale_style',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.appearance.style'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.appearance.style_help'))
                    ->choices(WholesaleStyleEnum::labels())
                    ->selected(WholesaleHelper::getStyle())
            )
            ->add(
                'colors_section',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content('<h4 class="mb-3 mt-4">' . trans('plugins/ecommerce-wholesale::wholesale.appearance.colors_section') . '</h4>')
            )
            ->add(
                'wholesale_primary_color',
                ColorField::class,
                ColorFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.appearance.primary_color'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.appearance.primary_color_help'))
                    ->defaultValue(WholesaleHelper::getPrimaryColor())
            )
            ->add(
                'wholesale_header_color',
                ColorField::class,
                ColorFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.appearance.header_color'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.appearance.header_color_help'))
                    ->defaultValue(WholesaleHelper::getHeaderColor())
            )
            ->add(
                'wholesale_price_color',
                ColorField::class,
                ColorFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.appearance.price_color'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.appearance.price_color_help'))
                    ->defaultValue(WholesaleHelper::getPriceColor())
            )
            ->add(
                'wholesale_badge_color',
                ColorField::class,
                ColorFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.appearance.badge_color'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.appearance.badge_color_help'))
                    ->defaultValue(WholesaleHelper::getBadgeColor())
            )
            ->add(
                'wholesale_border_color',
                ColorField::class,
                ColorFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.appearance.border_color'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.appearance.border_color_help'))
                    ->defaultValue(WholesaleHelper::getBorderColor())
            )
            ->add(
                'display_section',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content('<h4 class="mb-3 mt-4">' . trans('plugins/ecommerce-wholesale::wholesale.appearance.display_section') . '</h4>')
            )
            ->add(
                'wholesale_show_pricing_table',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.appearance.show_pricing_table'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.appearance.show_pricing_table_help'))
                    ->value($showPricingTableEnabled = WholesaleHelper::showPricingTable())
            )
            ->addOpenCollapsible('wholesale_show_pricing_table', '1', $showPricingTableEnabled)
            ->add(
                'wholesale_display_mode',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.appearance.display_mode'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.appearance.display_mode_help'))
                    ->choices(WholesaleDisplayModeEnum::labels())
                    ->selected(WholesaleHelper::getDisplayMode())
            )
            ->add(
                'wholesale_show_icon',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.appearance.show_icon'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.appearance.show_icon_help'))
                    ->value($showIconEnabled)
            )
            ->addOpenCollapsible('wholesale_show_icon', '1', $showIconEnabled)
            ->add(
                'wholesale_icon',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.appearance.icon'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.appearance.icon_help'))
                    ->choices(WholesaleHelper::getIconOptions())
                    ->selected(WholesaleHelper::getIcon())
            )
            ->addCloseCollapsible('wholesale_show_icon', '1')
            ->add(
                'wholesale_show_original_price',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.appearance.show_original_price'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.appearance.show_original_price_help'))
                    ->value(WholesaleHelper::showOriginalPrice())
            )
            ->add(
                'wholesale_show_savings',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.appearance.show_savings'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.appearance.show_savings_help'))
                    ->value($showSavingsEnabled)
            )
            ->addOpenCollapsible('wholesale_show_savings', '1', $showSavingsEnabled)
            ->add(
                'wholesale_savings_color',
                ColorField::class,
                ColorFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.appearance.savings_color'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.appearance.savings_color_help'))
                    ->defaultValue(WholesaleHelper::getSavingsColor())
            )
            ->addCloseCollapsible('wholesale_show_savings', '1')
            ->addCloseCollapsible('wholesale_show_pricing_table', '1')
            ->addCloseCollapsible('wholesale_enabled', '1');
    }
}
