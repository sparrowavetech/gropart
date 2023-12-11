<?php

namespace Botble\Base\Supports;

use Botble\Setting\Facades\Setting;

class AdminAppearance
{
    protected string $settingKey = 'admin_appearance';

    public function getCurrentLayout(): string
    {
        return $this->getSetting('layout', array_key_first($this->getLayouts()));
    }

    public function isVerticalLayout(): bool
    {
        return $this->getCurrentLayout() === 'vertical';
    }

    public function isHorizontalLayout(): bool
    {
        return $this->getCurrentLayout() === 'horizontal';
    }

    public function showMenuItemIcon(): bool
    {
        return $this->getSetting('show_menu_item_icon', true);
    }

    public function getLayouts(): array
    {
        return [
            'vertical' => trans('core/setting::setting.admin_appearance.vertical'),
            'horizontal' => trans('core/setting::setting.admin_appearance.horizontal'),
        ];
    }

    public function getContainerWidth(): string
    {
        return $this->getSetting('container_width', array_key_first($this->getContainerWidths()));
    }

    public function getContainerWidths(): array
    {
        return [
            'container-xl' => trans('core/setting::setting.admin_appearance.container_width.default'),
            'container-3xl' => trans('core/setting::setting.admin_appearance.container_width.large'),
            'container-fluid' => trans('core/setting::setting.admin_appearance.container_width.full'),
        ];
    }

    public function getSetting(string $key, mixed $default = null)
    {
        return Setting::get("{$this->settingKey}_{$key}", $default);
    }

    public function getSettingKey(string $key): string
    {
        return "{$this->settingKey}_{$key}";
    }

    public function setSetting(string|array $key, mixed $value = null): void
    {
        $data = is_array($key) ? $key : [$key => $value];

        foreach ($data as $k => $v) {
            Setting::set("{$this->getSettingKey($k)}", $v);
        }

        Setting::save();
    }
}
