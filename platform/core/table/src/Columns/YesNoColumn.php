<?php

namespace Botble\Table\Columns;

use Botble\Table\Contracts\FormattedColumn as FormattedColumnContract;
use Illuminate\Support\Facades\Blade;

class YesNoColumn extends FormattedColumn implements FormattedColumnContract
{
    public static function make(array|string $data = [], string $name = ''): static
    {
        return parent::make($data, $name)
            ->width(100);
    }

    public function formattedValue($value): string
    {
        return Blade::render(
            sprintf(
                '<x-core::badge label="%s" color="%s" />',
                $value ? trans('core/base::base.yes') : trans('core/base::base.no'),
                $value ? 'success' : 'danger'
            )
        );
    }
}
