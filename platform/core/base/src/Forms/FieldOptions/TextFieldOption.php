<?php

namespace Botble\Base\Forms\FieldOptions;

class TextFieldOption extends InputFieldOption
{
    public static function make(): static
    {
        return parent::make()
            ->maxLength(250);
    }

    public function maxLength(int $maxLength): static
    {
        if ($maxLength > 0) {
            $this->addAttribute('data-counter', $maxLength);
        }

        return $this;
    }
}
