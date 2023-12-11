<?php

namespace Botble\Shortcode\Forms\Fields;

use Botble\Base\Facades\Assets;
use Botble\Base\Forms\Fields\ColorField;

class ShortcodeColorField extends ColorField
{
    public function render(array $options = [], $showLabel = true, $showField = true, $showError = true): string
    {
        return Assets::scriptToHtml('coloris') . parent::render($options, $showLabel, $showField, $showError);
    }
}
