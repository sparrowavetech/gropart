<?php

namespace Botble\Widget\Widgets;

use Botble\Widget\AbstractWidget;
use Botble\Widget\Widgets\ValueObjects\CoreSimpleMenuItem;
use Illuminate\Support\Collection;

class CoreSimpleMenu extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => trans('packages/widget::widget.widget_menu'),
            'description' => trans('packages/widget::widget.widget_menu_description'),
            'items' => [],
        ]);

        $widgetDirectory = $this->getWidgetDirectory();

        $this->setFrontendTemplate('packages/widget::widgets.' . $widgetDirectory . '.frontend');
        $this->setBackendTemplate('packages/widget::widgets.' . $widgetDirectory . '.backend');
    }

    public function adminConfig(): array
    {
        $fields = [
            [
                'type' => 'text',
                'label' => trans('packages/widget::widget.widget_menu_label'),
                'label_attr' => ['class' => 'control-label required'],
                'attributes' => [
                    'name' => 'label',
                    'value' => null,
                    'options' => [
                        'class' => 'form-control',
                    ],
                ],
            ],
            [
                'type' => 'text',
                'label' => trans('packages/widget::widget.widget_menu_url'),
                'label_attr' => ['class' => 'control-label required'],
                'attributes' => [
                    'name' => 'url',
                    'value' => null,
                    'options' => [
                        'class' => 'form-control',
                    ],
                ],
            ],
            [
                'type' => 'text',
                'label' => trans('packages/widget::widget.widget_menu_attributes'),
                'label_attr' => ['class' => 'control-label'],
                'attributes' => [
                    'name' => 'attributes',
                    'value' => null,
                    'options' => [
                        'class' => 'form-control',
                        'placeholder' => 'rel="nofollow" aria-label="Home"',
                    ],
                ],
            ],
            [
                'type' => 'onOff',
                'label' => trans('packages/widget::widget.widget_menu_is_open_new_tab'),
                'label_attr' => ['class' => 'control-label'],
                'attributes' => [
                    'name' => 'is_open_new_tab',
                    'value' => null,
                    'options' => [
                        'class' => 'form-control',
                    ],
                ],
            ],
        ];

        return apply_filters('widget_menu_admin_config', [
            'fields' => $fields,
        ], $this);
    }

    public function data(): array|Collection
    {
        $items = $this->getConfig('items', []);

        if ($items === '[]') {
            $this->data['items'] = collect();

            return $this->data;
        }

        return array_merge($this->data, [
            'items' => collect($items)->map(function ($item) {
                return new CoreSimpleMenuItem($item);
            }),
        ]);
    }

    public function getWidgetDirectory(): string
    {
        return 'core-simple-menu';
    }
}
